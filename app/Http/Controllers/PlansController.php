<?php namespace App\Http\Controllers;

/**
 * Class PlansController
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
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PlansFormRequest;
use App\Http\Requests\ReviewFormRequest;
use App\Http\Requests\UserResponseFormRequest;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use PhpSpec\Exception\Exception;

use App\Facades\Email;

use App\Plans;
use App\Tickets;
use App\Testers;
use App\TicketsResponses;
use App\ActivityStream;
use App\User;
use App\Tables;

use Auth;
use Session;

class PlansController extends Controller
{
    const CONSTANT = 'constant value';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource
     *
     * @return \Illuminate\View\View|Redirect
     */
    public function build()
    {
        $user = Auth::user();

        return view('pages.testplanner.plan_build_step_1', ['userId' => $user->id]);
    }

    /**
     * View plan
     *
     * @param $id
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function view($id)
    {
        $plan = Plans::findOrFail($id);

        return view('pages.testplanner.plan_view', $plan);
    }

    /**
     * Show all plans or plans created by an administrator
     *
     * @param $userId
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function all($userId)
    {
        $userRoles = Auth::user()->role()->get();

        // Display all plans
        $query = '';
        foreach($userRoles as $role) {
            if ($role->name == "Administrator") {
                $sorting = Tables::sorting();
                $table   = Plans::prepareTable($sorting['order'], [
                    'description',
                    'first_name',
                    'status',
                    'created_at',
                    'updated_at'
                ]);

                $query = Plans::getAllPlans($sorting['sortBy'], $sorting['order'], $userId);
                break;
            }
        }

        return view('pages.testplanner.plans', [
            'userId'      => isset($userId) ? $userId : 0,
            'plans'       => isset($query) ? $query->paginate(config('testplanner.pagination_count')) : '',
            'totalPlans'  => isset($query) ? Plans::count() : 0,
            'columns'     => $table['columns'],
            'columnsLink' => $table['columns_link'],
            'link'        => ''
        ]);
    }

    public function search()
    {

    }

    /**
     * Store a newly created resource in storage
     *
     * @param PlansFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function store(PlansFormRequest $request)
    {
        // Save Plan data to session
        Session::put('mophie_testplanner.plan', array_except($request->all(), '_token'));

        return redirect('ticket/build');
    }

    /**
     * View response by plan and user ID
     *
     * @param $planId
     * @param $userId
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function viewResponse($planId, $userId)
    {
        $plan       = Plans::getPlanResponses($planId, $userId);
        $allTesters = Testers::getTestersByPlanId($planId);

        $browserTesters[''] = 'Select other users';
        foreach ($allTesters as $tester) {
            $browserTesters[$tester->id] = $tester->first_name;
        }

        return view('pages.testplanner.plan_view_response', [
            'userId'  => $userId,
            'plan'    => $plan,
            'testers' => $browserTesters
        ]);
    }

    /**
     * Display or edit plan
     *
     * @param $planId
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($planId)
    {
        $user = Auth::user();
        $plan = Plans::getPlanResponses($planId, $user->id);

        return view('pages.testplanner.plan_respond', ['plan' => $plan]);
    }

    /**
     * Show complete information of all forms filled on each
     * registration step
     *
     * @return \Illuminate\View\View
     */
    public function review()
    {
        // Session data
        $data = [
            'plan'    => Session::get('mophie_testplanner.plan'),
            'tickets' => Session::get('mophie_testplanner.tickets'),
            'testers' => Session::get('mophie_testplanner.testers')
        ];

        return view('pages.testplanner.plan_build_review', $data);
    }

    /**
     * Finalize Plan setup
     *
     * @param ReviewFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(ReviewFormRequest $request)
    {
        // Retrieve session data
        $planData    = Session::get('mophie_testplanner.plan');
        $ticketsData = Session::get('mophie_testplanner.tickets');
        $testerData  = Session::get('mophie_testplanner.testers');
        $redirect    = false;
        $errorMsg    = '';

        // Start transaction
        DB::beginTransaction();

        // Start plan creation
        try {
            // Save new plan build
            $plan = Plans::create($planData);
            $planId = $plan->id;
            $planData['id'] = $planId;

            if (isset($plan->id)) {
                // Save new tickets
                Tickets::create([
                    'plan_id' => $planId,
                    'tickets' => serialize($ticketsData)
                ]);

                // Save new testers
                foreach($testerData as $tester) {
                    Testers::create([
                        'plan_id'   => $planId,
                        'tester_id' => $tester['id'],
                        'browser'   => $tester['browser']
                    ]);

                    // Create object for email
                    $testersWithEmail[] = [
                        'plan_desc'  => $plan->description,
                        'tester_id'  => $tester['id'],
                        'first_name' => $tester['first_name'],
                        'browser'    => $tester['browser'],
                        /*'email'      => $tester['email']*/
                    ];
                }
            }
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $redirect = true;
        } catch (ValidationException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        } catch (QueryException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        } catch (ModelNotFoundException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        }

        // Redirect if errors
        /*if ($redirect) {
            // Rollback
            DB::rollback();

            // Log to system
            Utils::log($errorMsg, $mergedData);

            // Delete session
            Session::forget('mophie_h2pro');

            return redirect()->action('RegistrationController@index')
                ->with('flash_message', config('h2pro.registration_problems'));
        }*/

        // Commit all changes
        DB::commit();

        // Log activity
        ActivityStream::saveActivityStream($planData, 'plan');

        // Mail all test browsers
        //Email::sendEmail('plan-created', array_merge(array('plan' => $planData), array('testers' => $testersWithEmail)));

        return redirect('dashboard');
    }

    /**
     * Save user's plan response
     *
     * @param UserResponseFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function saveUserResponse(UserResponseFormRequest $request)
    {
        $res      = array_except($request->all(), '_token');
        $planData = json_decode($res['plan'], true);
        $tickets  = json_decode($res['tickets-obj'], true);
        $planData['tickets_responses'] = $tickets;

        // Start transaction
        DB::beginTransaction();

        try {
            // Save ticket response
            $response = TicketsResponses::saveTicketResponse($planData);

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $redirect = true;
        } catch (ValidationException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        } catch (QueryException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        } catch (ModelNotFoundException $e) {
            $errorMsg = $e->getErrors();
            $redirect = true;
        }

        // Commit all changes
        DB::commit();

        // Redirect if errors
        /*if ($redirect) {
            // Rollback
            DB::rollback();

            // Log to system
            Utils::log($errorMsg, $mergedData);

            return redirect()->action('PlansController@index')
                ->with('flash_message', config('h2pro.registration_problems'));
        }*/

        // Log activity
        ActivityStream::saveActivityStream($planData, 'ticket-response', $response);

        // Mail all test browsers
        /*if ($planStatus == 'complete') {
            // Create object for email
            Email::sendEmail('ticket-responded', [
                'ticket_resp_id'    => $resp['ticket_resp_id'],
                'plan_desc'         => $planData['description'],
                'tester_id'         => $planData['tester_id'],
                'tester_first_name' => $planData['assignee'],
                'email'             => $user->email,
                'ticket_status'     => $ticketStatus,
                'tickets'           => serialize($tickets))
            ]);
        }*/

        return redirect('dashboard');
    }
}