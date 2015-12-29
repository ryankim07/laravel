{{--
|--------------------------------------------------------------------------
| Admin registrations list
|--------------------------------------------------------------------------
|
| This template is used when showing all registrations.
|
--}}

@extends('layout.admin.master')

@section('content')

    <div class="col-xs-12 col-md-12" id="main">

        {!! Form::open(['route' => 'plan.search', 'class' => 'form-horizontal', 'role' => 'search']) !!}

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="clearfix">
                    <div class="pull-left"><h3>Plans <span class="badge">{!! $totalPlans !!}</span></h3></div>
                    <div class="pull-right">
                        {!! Form::select('view_user_type', ['0' => 'All', Auth::user()->id => 'My Plans'], $userId, ['id' => 'view_user_type']) !!}
                    </div>
                </div>
            </div>
            <div class="panel-body">

                @if (count($plans) > 0)

                    <div class="row table-options">
                        <div class="pull-right">
                            {!! Form::button('Search', ['class' => 'btn btn-success', 'type' => 'submit']) !!}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">

                            @include('pages.main.partials.table_header', $columns)

                            <tbody>

                            @foreach($plans as $plan)

                                <tr class="toggler" data-url="{!! URL::route('plan.view', $plan->id) !!}">
                                    <td>{!! $plan->description !!}</td>
                                    <td>{!! $plan->first_name !!}</td>
                                    <td>{!! $plan->status !!}</td>
                                    <td>{!! Utils::dateConverter($plan->created_at) !!}</td>
                                    <td>{!! Utils::dateConverter($plan->updated_at) !!}</td>
                                </tr>

                            @endforeach

                            </tbody>
                        </table>

                        {!! $plans->appends($link)->render() !!}

                    </div>

                @else

                    <p>No records found.</p>

                @endif

            </div>
        </div>

        {!! Form::close() !!}

    </div>

<script type="text/javascript">

$(document).ready(function() {
    // Open viewer for dropdown
    $('#view_user_type').on('change', function (e) {
        var route = "{!! URL::route('plan.view.all', null) !!}";
        var adminId = $(this).val();
        window.location.href =  route + '/' + adminId;
    });
});

</script>

@stop