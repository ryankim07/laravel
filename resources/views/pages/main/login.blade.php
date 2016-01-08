{{--
|--------------------------------------------------------------------------
| Mian login
|--------------------------------------------------------------------------
|
| This template is used when showing login form.
|
--}}

@extends('layout.main.master')

@section('content')

    <div class="col-xs-12 col-md-6 main" id="login-main">

        {!! Form::open(['route' => 'auth.post.login', 'class' => 'form-horizontal', 'id' => 'auth-login-form']) !!}

        <div class="panel panel-primary col-md-8">
            <div class="panel-body">
                <h3 class="sub-header">Login</h3>

                @include('errors.list')

                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Form::label('email_label', 'E-Mail Address / Username') !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control input-sm', 'id' => 'email']) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Form::label('password_label', 'Password') !!}
                        {!! Form::password('password', ['class' => 'form-control input-sm', 'id' => 'password']) !!}
                    </div>
                </div>

                @include('pages/main/partials/submit_button', [
                    'submitBtnText' => 'Login',
                    'direction'     => 'pull-left',
                    'class'		    => 'btn-primary',
                    'id'			=> 'login-btn'
                ])

                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Form::checkbox('remember', 1, false, ['class' => '', 'aria-required' => 'true', 'id' => 'remember']) !!}
                        {!! Form::label('Remember me') !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Html::linkRoute('password.email', 'Forgot Your Password?') !!}
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}

    </div>

@stop
