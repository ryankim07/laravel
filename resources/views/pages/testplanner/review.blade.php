{{--
|--------------------------------------------------------------------------
| Review new created plan
|--------------------------------------------------------------------------
--}}

@extends('layout.main.master')

@section('content')

    <div class="col-xs-12 col-md-12 main plan-wizard" id="review-main">

        {!! Form::open(['route' => 'plan.save', 'class' => 'form-horizontal', 'id' => 'plan-review-form']) !!}

        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-12 col-md-8">
                        <i class="fa fa-cubes fa-3x header-icon"></i>
                        <h4>Review</h4>
                    </div>
                    <div class="col-md-4">
                        <div class="progress">
                            <div class="progress-bar progress-bar-primary progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body">

                @include('errors.list')

                <div class="page-header"><h4>Plan Details</h4></div>
                <div class="row nested-block">
                    <a href="{!! URL::route('plan.edit') !!}" class="pencil" title="Edit"><i class="fa fa-pencil fa-lg"></i></a>
                    <ul class="list-unstyled fa-ul">
                        <li><i class="fa-li fa fa-tag"></i> <h5>Description:</h5></li>
                        <li class="review-text">{!! $plan['description'] !!}</li>
                        <li><i class="fa-li fa fa-calendar"></i> <h5>Starts on:</h5></li>
                        <li class="review-text">{!! $plan['started_at'] !!}</li>
                        <li><i class="fa-li fa fa-calendar"></i> <h5>Expires on:</h5></li>
                        <li class="review-text">{!! $plan['expired_at'] !!}</li>
                    </ul>
                </div>
                <div class="page-header tickets"><h4>Tickets</h4></div>
                @foreach($tickets as $ticket)
                    <div class="row nested-block ticket-row" id="{!! $ticket['id'] !!}">
                        <a href="#" class="trash" data-id="{!! $ticket['id'] !!}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
                        <a href="{!! URL::route('ticket.edit') !!}" class="pencil" title="Edit"><i class="fa fa-pencil fa-lg"></i></a>
                        <ul class="list-unstyled fa-ul">
                            <li><i class="fa-li fa fa-tag"></i> <h5>Description</h5></li>
                            <li class="review-text">{!! $ticket['desc'] !!}</li>
                            <li><i class="fa-li fa fa-star"></i> <h5>Objective</h5></li>
                            <li class="review-text">{!! $ticket['objective'] !!}</li>
                            <li><i class="fa-li fa fa-sort-numeric-asc"></i> <h5>Test Steps</h5></li>
                            <li class="review-text">{!! nl2br($ticket['test_steps']) !!}</li>
                        </ul>
                    </div>
                @endforeach
                <div class="page-header testers"><h4>Browser Testers</h4></div>
                <div class="row nested-block">
                    <a href="{!! URL::route('tester.edit') !!}" class="pencil" title="Edit"><i class="fa fa-pencil fa-lg"></i></a>
                    @include('pages/testplanner/partials/testers', $users)
                </div>
            </div>
        </div>

        @include('pages/main/partials/submit_and_button', [
            'direction'   => 'pull-right',
            'btnText'     => 'Restart',
            'btnClass'    => 'btn-primary',
            'btnId'       => 'back-btn',
            'submitText'  => 'Finalize',
            'submitClass' => 'btn-primary',
            'submitId'    => 'continue-btn'
        ])

        {!! Form::close() !!}

    </div>

    <script type="text/javascript">

        $(document).ready(function() {

            // Load ticket builder
            var ticketBuilder = new TicketBuilder({
                formIdName:    'review-main',
                ticketRowName: 'ticket-row',
                removeBtnName: 'trash'
            });

            // Remove ticket by Ajax
            ticketBuilder.removeAjax("{!! URL::to('ticket/remove') !!}");

            // Preselect testers checkbox input
            preCheckBrowserTesters('<?php echo $testers ?>', 'review');

            // Restart plan building
            backButtonSubmit('{!! URL::route('plan.build') !!}');
        });

    </script>

@stop