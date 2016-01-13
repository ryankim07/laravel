{{--
|--------------------------------------------------------------------------
| Step 2 - Build or Edit tickets
|--------------------------------------------------------------------------
--}}

@extends('layout.main.master')

@section('content')

    <div class="col-xs-12 col-md-12 main" id="step-2-main">
        @if($plan['mode'] == 'build')
            {!! Form::open(['route' => 'ticket.store', 'class' => 'form-horizontal', 'id' => 'ticket-build-form']) !!}
        @else
            {!! Form::model($plan, ['method' => 'PATCH', 'action' => ['TicketsController@update'], 'class' => 'form-horizontal', 'id' => 'ticket-edit-form']) !!}
        @endif
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h4>Step 2 of 3 - {!! $plan['mode'] == 'build' ? 'Add tickets to be tested' : 'Edit tickets to be tested' !!}</h4>
                    </div>
                    @if($plan['mode'] == 'build')
                        <div class="col-md-4">
                            <div class="progress">
                                <div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 45%">45%</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="panel-body">

                @include('errors.list')

                @if($plan['mode'] == 'build')
                    @include('pages/testplanner/partials/tickets', ['mode' => $plan['mode']])
                @else
                    {!! $plan['tickets_html'] !!}
                @endif
            </div>
        </div>

        @if($plan['mode'] == 'build')
            @include('pages/main/partials/submit_button', [
                'submitBtnText' => 'Continue',
                'direction'     => 'pull-right',
                'class'		    => 'btn-primary',
                'id'			=> 'continue-btn'
            ])
        @else
            @include('pages/main/partials/update_back_cancel_button', [
                'direction'     => 'pull-right',
                'class'		    => 'btn-primary',
                'updateBtnText' => 'Update',
                'updateBtnId'	=> 'update-btn',
                'backBtnText'   => 'Go Back',
                'backBtnId'		=> 'back-btn'
            ])
        @endif

        {!! Form::close() !!}

    </div>

    <script type="text/javascript">

        $(document).ready(function() {
            /**
             *  CREATING NEW TICKETS
             */
            var ticketBuilder = new TicketBuilder({
                mode: 'build',
                formIdName: 'step-2-main',
                ticketRowName: 'ticket-row',
                ticketDescName: 'ticket-description',
                objectiveName: 'objective',
                testStepsName: 'test-steps',
                ticketsObjName: 'tickets_obj',
                addBtnName: 'add-ticket-btn',
                removeBtnName: 'trash',
                continueBtnName:'continue-btn',
                updateBtnName: 'update-btn',
                clearBtnName: 'clear-btn',
                jira: <?php echo $plan['jira_issues']; ?>
            });

            // Load ticket builder
            ticketBuilder.load();
        });

    </script>

@stop