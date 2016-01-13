<?php namespace App\Http\Controllers;

/**
 * Class TicketsController
 *
 * Controller
 *
 * @author     Ryan Kim
 * @category   Mophie
 * @package    Test Planner
 * @copyright  Copyright (c) 2016 mophie (https://lpp.nophie.com)
 */

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\TicketsFormRequest;
use App\Http\Requests\UserResponseFormRequest;
use PhpSpec\Exception\Exception;

use App\Facades\Jira;

use App\Tickets;
use App\TicketsResponses;
use App\ActivityStream;

use Session;

class TicketsController extends Controller
{
    /**
     * TicketsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('testplanner', [
            'only' => ['build', 'edit']
        ]);
    }

    /**
     * Show the form for creating a new resource
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function build()
    {
        // Get Jira issues
        $jiraIssues = $this->_Jira();

        $ticketsHtml = view('pages/testplanner/partials/tickets', [
            'mode'     => 'create',
            'ticket[]' => [
                'id'          => '',
                'description' => '',
                'objective'   => '',
                'test_steps'  => ''
            ]
        ])->render();

        return view('pages.testplanner.step_2', [
            'plan' => [
                'mode'          => 'build',
                'tickets_html'  => $ticketsHtml,
                'jira_issues'   => json_encode($jiraIssues)
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
        // Get tickets session data
        $ticketsData = Session::get('mophie_testplanner.tickets');

        $ticketsHtml = '';
        foreach($ticketsData as $ticket) {
            $ticketsHtml .= view('pages/testplanner/partials/tickets', [
                'mode'   => 'edit',
                'ticket' => $ticket
            ])->render();
        }

        // Get Jira issues
        $jiraIssues = $this->_Jira();

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
        $tickets = json_decode($request->get('tickets_obj'), true);

        // Save tickets data to session
        Session::put('mophie_testplanner.tickets', $tickets);

        return redirect('plan/review');
    }

    /**
     * Save user's plan response
     *
     * @param UserResponseFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function save(UserResponseFormRequest $request)
    {
        $planData = json_decode($request->get('plan'), true);
        $tickets  = json_decode($request->get('tickets_obj'), true);
        $planData['tickets_responses'] = $tickets;

        // Save ticket response
        $response = TicketsResponses::saveResponse($planData);

        // Log activity
        ActivityStream::saveActivityStream($planData, 'ticket-response', $response);

        // Mail all test browsers
        /*Email::sendEmail('ticket-response', [
                'ticket_resp_id'    => $resp['ticket_resp_id'],
                'plan_desc'         => $planData['description'],
                'tester_id'         => $planData['tester_id'],
                'tester_first_name' => $planData['assignee'],
                'email'             => $user->email,
                'ticket_status'     => $ticketStatus,
                'tickets'           => serialize($tickets))
        ]);*/

        return redirect('dashboard');
    }

    /**
     * Update all the ticket details
     *
     * @param $planId
     * @param Request $request
     */
    public function updateDetails($planId, Request $request)
    {
        $plan = Tickets::find($planId);
        $plan->update(['description' => $request->get('description')]);
    }

    /**
     * Store a newly created resource in storage
     *
     * @param TicketsFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function store(TicketsFormRequest $request)
    {
        $tickets = json_decode($request->get('tickets_obj'), true);

        // Save tickets data to session
        Session::put('mophie_testplanner.tickets', $tickets);

        return redirect('tester/build');
    }

    /**
     * Remove tickets from review page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAjax(Request $request)
    {
        // Get tickets session data
        $ticketsData = Session::get('mophie_testplanner.tickets');

        foreach($ticketsData as $ticket) {
            $modifiedData[$ticket['id']] = $ticket;
        }

        // Remove
        unset($modifiedData[$request->get('ticketId')]);

        // Save plan data to session
        Session::put('mophie_testplanner.tickets', $modifiedData);

        return response()->json('success');
    }

    /**
     * Use Jira API
     *
     * @return array
     */
    private function _Jira()
    {
        // Get JIRA issues
        $results = Jira::getAllIssues('ECOM');
        $issues  = [];

        if (isset($results)) {
            foreach ($results as $issue) {
                $issues[] = $issue['key'] . ': ' . $issue['summary'];
            }
        }

        return $issues;
    }
}