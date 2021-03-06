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

    <div class="col-xs-12 col-md-4 col-md-offset-4 main" id="login-main">

        {!! Form::open(['route' => 'auth.post.login', 'class' => 'form-horizontal', 'id' => 'auth-login-form']) !!}

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Login</h4>
            </div>
            <div class="panel-body">

                @include('errors.list')

                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Form::label('email_label', 'E-Mail Address / Username') !!}
                        <div class="input-group">
                            {!! Form::email('email', old('email'), ['class' => 'form-control input-sm', 'id' => 'email']) !!}
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12 col-md-8">
                        {!! Form::label('password_label', 'Password') !!}
                        <div class="input-group">
                            {!! Form::password('password', ['class' => 'form-control input-sm', 'id' => 'password']) !!}
                            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-key"></i></span>
                        </div>
                    </div>
                </div>

                @include('pages/main/partials/submit', [
                    'btnText'   => 'Login',
                    'direction' => 'pull-left',
                    'class'		=> 'btn-custom btn-sm',
                    'id'		=> 'login-btn'
                ])

                <div class="form-group">
                    <div class="col-xs-12 col-md-6">
                        {!! Form::checkbox('remember', 1, false, ['class' => '', 'aria-required' => 'true', 'id' => 'remember']) !!}
                        {!! Form::label('Remember me') !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12 col-md-6">
                        {!! Html::linkRoute('password.email', 'Forgot Your Password?') !!}
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}

    </div>

@stop