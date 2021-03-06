{{--
|--------------------------------------------------------------------------
| Step 1 - Build or Edit plan
|--------------------------------------------------------------------------
--}}

@extends('layout.main.master')

@section('content')

	<div class="col-xs-12 col-md-12 main plan-wizard" id="step-1-main">
        @if($mode == 'build')
		    {!! Form::open(['route' => 'plan.store', 'class' => 'form-horizontal', 'id' => 'plan-build-form']) !!}
        @else
			{!! Form::model($planData, ['method' => 'PATCH', 'action' => ['PlansController@update'], 'class' => 'form-horizontal', 'id' => 'plan-edit-form']) !!}
	    @endif
			{!! Form::hidden('jira_bvid', null, ['id' => 'jira-bvid']) !!}
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="row">
					<div class="col-md-8">
						<i class="fa fa-cubes fa-3x header-icon"></i>
						<h4>Step 1 of 3 - {!! $mode == 'build' ? 'Start building plan' : 'Edit plan' !!}</h4>
					</div>
					@if($mode == 'build')
						<div class="col-md-4">
							<div class="progress">
								<div class="progress-bar progress-bar-user progress-bar-striped active" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" style="width: 15%">15%</div>
							</div>
						</div>
					@endif
				</div>
			</div>
			<div class="panel-body">

				@include('errors.list')

				@if($mode == 'build')
				    {!! Form::hidden('creator_id', $userId) !!}
				@else
					{!! Form::hidden('creator_id', $planData['creator_id']) !!}
                @endif

				<div class="page-header"><h4>Plan Details</h4></div>
				<div class="row nested-block">
					@include('pages/testplanner/partials/plan')
				</div>
			</div>
		</div>

		@if($mode == 'build')
            @include('pages/main/partials/submit', [
                'btnText'   => 'Continue',
                'direction' => 'pull-right',
                'class'		=> 'btn-primary',
                'id'		=> 'continue-btn'
            ])
        @else
			@include('pages/main/partials/submit_and_button', [
                'direction'   => 'pull-right',
                'btnText'     => 'Go Back',
                'btnClass'    => 'btn-primary',
                'btnId'       => 'back-btn',
                'submitText'  => 'Update',
                'submitClass' => 'btn-primary',
                'submitId'    => 'update-btn'
            ])
        @endif

		{!! Form::close() !!}

	</div>

	<script type="text/javascript">

		$(document).ready(function() {
			@if($mode != 'build')
				$('#step-1-main #plan-description').prop('readonly', true);
				$('#step-1-main .clear-btn').hide();
			@endif

			// Jira versions
			jiraVersions('step-1-main', 'plan-description', <?php echo $jira_versions; ?>);

			// Start and expiration dates
			planStartExpireDates();

			// Back button
			@if($mode != 'build')
                backButtonSubmit('{!! URL::previous() !!}');
			@endif
		});

	</script>

@stop