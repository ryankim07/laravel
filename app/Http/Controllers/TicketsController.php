<?php namespace App\Http\Controllers;

/**
 * Class TicketsController
 *
 * Controller
 *
 * @author     Ryan Kim
 * @category   Mophie
 * @package    Test Planner
 * @copyright  Copyright (c) 2016 mophie (https://tp.nophie.us)
 */

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\TicketsFormRequest;

use App\Events\RespondingPlan;

use App\Facades\Tools;

use App\Api\PlansApi,
    App\Api\TicketsApi,
    App\Api\TicketsResponsesApi,
    App\Api\JiraApi;

use Session;

class TicketsController extends Controller
{
    /**
     * @var PlansApi
     */
    protected $plansApi;

    /**
     * @var JiraApi
     */
    protected $jiraApi;


    /**
     * TicketsController constructor
     */
    public function __construct(PlansApi $plansApi, JiraApi $jiraApi)
    {
        $this->middleware('auth');
        $this->middleware('testplanner', ['only' => ['build', 'edit']]);
        $this->plansApi = $plansApi;
        $this->jiraApi  = $jiraApi;
    }

    /**
     * Show the form for creating a new resource
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function build()
    {
        // Grab Jira build version ID
        $buildVersionId = Session::get('mophie_testplanner.plan.jira_bvid');

        if (!$this->plansApi->checkPlanJiraBuildVersion($buildVersionId)) {
            return redirect('plan/build')->with('flash_error', config('testplanner.messages.plan.build_exists'));
        }

        // Get Jira issues
        $jiraIssues  = $this->jiraApi->jiraIssuesByVersion($buildVersionId);
        $ticketsHtml = '';

        foreach($jiraIssues['specificIssues'] as $issue) {
            $ticketsHtml .= view('pages/testplanner/partials/tickets', [
                'mode'   => 'custom',
                'ticket' => ['desc' => Tools::convertDoubleQuotes($issue)]
            ])->render();
        }

        return view('pages.testplanner.step_2', [
            'plan' => [
                'mode'         => 'build',
                'tickets_html' => $ticketsHtml,
                'jira_issues'  => json_encode($jiraIssues['allIssues']),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function edit()
    {
        // Get from session data
        $ticketsData = Session::get('mophie_testplanner.tickets');

        $ticketsHtml = '';
        foreach($ticketsData as $ticket) {
            $ticketsHtml .= view('pages/testplanner/partials/tickets', [
                'mode'             => 'edit',
                'ticket'           => $ticket,
                'addTicketBtnType' => 'btn-primary'
            ])->render();
        }

        // Get Jira issues
        $jiraIssues = $this->jiraApi->jiraIssues();

        return view('pages.testplanner.step_2', [
            'plan' => [
                'mode'         => 'edit',
                'tickets_html' => $ticketsHtml,
                'jira_issues'  => json_encode($jiraIssues)
            ]
        ]);
    }

    /**
     * Update the specified resource in storage
     *
     * @param TicketsFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TicketsFormRequest $request)
    {
        // Save data to session
        $tickets = json_decode($request->get('tickets_obj'), true);

        foreach($tickets as $ticket) {
            $ticket['desc'] = Tools::convertDoubleQuotes($ticket['desc']);
            $results[] = $ticket;
        }

        Session::put('mophie_testplanner.tickets', $results);

        return redirect('plan/review');
    }

    /**
     * Store a newly created resource in storage
     *
     * @param TicketsFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function store(TicketsFormRequest $request)
    {
        // Save data to session
        $tickets = json_decode($request->get('tickets_obj'), true);

        foreach($tickets as $ticket) {
            $ticket['desc'] = Tools::convertDoubleQuotes($ticket['desc']);
            $results[] = $ticket;
        }

        Session::put('mophie_testplanner.tickets', $results);

        return redirect('tester/build');
    }

    /**
     * Save user's response
     *
     * @param Request $request
     * @param TicketsResponsesApi $trApi
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function save(Request $request, TicketsResponsesApi $trApi)
    {
        $planData  = Session::get('mophie.plan');
        $tickets   = json_decode($request->get('tickets_obj'), true);
        $planData += ['tickets_responses' => $tickets];

        // Save ticket response
        $response = $trApi->saveResponse($planData);

        if (!$response) {
            return redirect()->action('PlansController@respond')
                ->withInput()
                ->withErrors(['message' => config('testplanner.messages.tickets.response_error')]);
        }

        // Send notifications observer
        $planData += ['tickets_overall_status' => $response];

        event(new respondingPlan($planData));

        return redirect('dashboard')->with('flash_success', config('testplanner.messages.tickets.response_success'));
    }

    /**
     * Ajax remove tickets from review page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeTicketAjax(Request $request, TicketsApi $ticketsApi)
    {
        // Get tickets session data
        $ticketsData = Session::get('mophie_testplanner.tickets');
        $ticketId    = $request->get('ticketId');

        $modifiedData = $ticketsApi->removeTicketFromSession($ticketsData, $ticketId);

        // Save plan data to session
        Session::put('mophie_testplanner.tickets', $modifiedData);

        return response()->json('success');
    }
}