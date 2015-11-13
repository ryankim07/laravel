{{--
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
|
| This template is used when 404 errors occurs.
|
--}}

@extends('layout.main.master')
@section('body-class','error')
@section('content')

<div class="content">
	<h2><strong>404</strong>It appears this link got wet. <br class="hidden-xs">We’ll get that fixed right away!</h2>
    {!! Html::link('/', 'Go to home page', ['class' => 'green-btn']) !!}
</div>

@stop