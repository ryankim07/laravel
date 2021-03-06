{{--
|--------------------------------------------------------------------------
| Edit plan
|--------------------------------------------------------------------------
--}}

@extends('layout.main.master')

@section('content')

	<div class="col-xs-12 col-md-12 main" id="view-main">

		{!! Form::model($plan, ['method' => 'PATCH', 'action' => ['PlansController@updateBuiltPlan', $plan['id']], 'class' => 'form-horizontal', 'id' => 'plan-edit-form']) !!}
		{!! Form::hidden('orig_data', $plan['orig_data']) !!}}

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-10 col-md-10">
						<i class="fa fa-pencil-square-o fa-3x header-icon"></i>
						<h4>Edit plan - {!! $plan['description'] !!}</h4>
					</div>
				</div>
			</div>
			<div class="panel-body">

				@include('errors.list')

                <div class="page-header"><h4>Plan Details</h4></div>
                <div class="row nested-block">
				    @include('pages/testplanner/partials/plan')
				</div>
                <div class="page-header" id="tickets-header"><h4>Tickets</h4></div>
                	{!! $plan['tickets_html'] !!}
                <div class="page-header">
					<h4 class="pull-left">Browsers</h4>
					<h6 class="pull-right"><em>* Disable options contain responses</em></h6>
					<div class="clearfix"></div>
				</div>
                <div class="row nested-block">
                    @include('pages/testplanner/partials/testers', ['users' => $plan['users']])
                </div>
			</div>
		</div>

		@include('pages/main/partials/submit_and_button', [
            'direction'   => 'pull-right',
            'btnText'     => 'Go Back',
            'btnClass'    => 'btn-primary',
            'btnId'       => 'back-btn',
            'submitText'  => 'Update',
            'submitClass' => 'btn-primary',
            'submitId'    => 'update-btn'
        ])

		{!! Form::close() !!}

		@include('pages/main/modal_window')

	</div>

	<script type="text/javascript">

		$(document).ready(function() {

			$('#view-main #plan-description').prop('readonly', true);
			$('#step-1-main .clear-btn').hide();

			// Load Jira versions, issues
			jiraVersions('view-main', 'plan-description', <?php echo $plan['jira_versions']; ?>);
			jiraIssues('view-main', 'ticket-description',<?php echo $plan['jira_issues']; ?>);

			// Fill expiration date
			planStartExpireDates();

			// Create new tickets
			var ticketBuilder = new TicketBuilder({
				mode:            'edit',
				formIdName:      'view-main',
				ticketRowName:   'ticket-row',
				ticketDescName:  'ticket-description',
                objectiveName:   'objective',
                testStepsName:   'test-steps',
                ticketsObjName:  'tickets_obj',
                addBtnName:      'add-ticket-btn',
                removeBtnName:   'trash',
                continueBtnName: 'continue-btn',
                updateBtnName:   'update-btn',
				clearBtnName:    'clear-btn'
			});

			// Load ticket builder
			ticketBuilder.load();

            // Preselect testers checkbox input
            preCheckBrowserTesters('<?php echo $plan['testers'] ?>', 'plan-edit');

            // Prepare data when submitting
            grabBrowserTesters('view-main');

			// Back button
			backButtonSubmit('{!! URL::previous() !!}');
		});

	</script>
@stop