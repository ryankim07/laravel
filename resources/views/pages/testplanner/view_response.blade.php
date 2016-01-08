{{--
|--------------------------------------------------------------------------
| Admin view plan
|--------------------------------------------------------------------------
|
| This template is used when viewing customer.
|
--}}

@extends('layout.main.master')

@section('content')

    <div class="col-xs-12 col-md-12 main" id="view-response-main">

        {!! Form::open(['route' => 'plan.view.response', 'id' => 'plan-user-response-form']) !!}
        {!! Form::hidden('plan', json_encode($plan)) !!}
        {!! Form::hidden('plan_id', $plan['id'], ['id' => 'plan_id']) !!}
        {!! Form::hidden('ticket_resp_id', $plan['ticket_resp_id']) !!}

        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-10 col-md-10">
                        <i class="fa fa-commenting fa-4x header-icon"></i>
                        <h3>View Response - {!! $plan['description'] !!}</h3>
                    </div>
                    <div class="col-xs-2 col-md-2">
                        {!! Form::select('tester', $testers, null, ['class' => 'form-control input-sm', 'id' => 'view-tester', 'data-url' => route('plan.view.response', ['id' => null])]) !!}
                    </div>
                </div>
            </div>
            <div class="panel-body">

                @include('errors.list')

                @if(empty($plan['ticket_resp_id']))
                    <p>{!! $plan['assignee'] !!}, {!! config('testplanner.plan_non_user_response') !!}</p>
                @else
                    <div class="row nested-block">
                        <legend>Plan Details</legend>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <p>Reporter: <strong>{!! $plan['reporter'] !!}</strong></p>
                                <p>Assignee: <strong>{!! $plan['assignee'] !!}</strong></p>
                                <p>Status:

                                    <?php
                                        if($plan['ticket_status'] == 'complete') {
                                            $trLabel = 'label-default';
                                        } else if($plan['ticket_status'] == 'progress') {
                                            $trLabel = 'label-warning';
                                        } else {
                                            $trLabel = 'label-success';
                                        }
                                    ?>

                                    <span class="label {!! $trLabel !!}">{!! $plan['ticket_status'] !!}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-8">
                            <div class="form-group">
                                <p>Created: <strong>{!! $plan['created_at'] !!}</strong></p>
                                <p>Updated: <strong>{!! $plan['updated_at'] !!}</strong></p>
                            </div>
                        </div>
                    </div>
                    @foreach($plan['tickets'] as $ticket)
                        <div class="row nested-block ticket-panel">
                            <legend>Ticket - {!! Html::link($ticket['description_url'], $ticket['description'], ['target' => '_blank', 'title' => 'Click to view issue in Jira']) !!}</legend>
                            <div class="col-xs-12 col-md-8">
                                <div class="form-group">
                                    <legend>Objective</legend>
                                    <p><span>{!! $ticket['objective'] !!}</span></p>
                                </div>
                                <div class="form-group">
                                    <legend>Steps to test</legend>
                                    <p><span>{!! nl2br($ticket['test_steps']) !!}</span></p>
                                </div>
                                <div class="form-group">
                                    <legend>Status</legend>

                                    <?php
                                    $passed = '';
                                    $failed = '';
                                    if (isset($ticket['test_status'])) {
                                        $passed = $ticket['test_status'] == 1 ? true : '';
                                        $failed = $ticket['test_status'] == 0 ? true : '';
                                    }
                                    ?>

                                    <p>
                                        <span>
                                        @if($passed)
                                                Passed
                                            @elseif($failed)
                                                Failed
                                            @endif
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <legend>Notes</legend>

                                    {!! isset($ticket['notes_response']) ? nl2br($ticket['notes_response']) : null !!}

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

        {!! Form::close() !!}

    </div>

@stop