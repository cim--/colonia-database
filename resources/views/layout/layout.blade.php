<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

		<title>@yield ('title')</title>
		<link rel="stylesheet" href="/css/cdb.css" type="text/css">
		<script type='text/javascript' src='/js/cdb.js'></script>
    </head>
    <body>
      <div id='main'>
		<nav>
		  <div class='row'>
			<div class='col-sm-1'><a href="{{route('index')}}">Home</a></div>
			<div class='col-sm-1'><a href="{{route('distances')}}">Distances</a></div>
			<div class='col-sm-1'><a href="{{route('history')}}">History</a></div>
            <div class='col-sm-7'></div>
			@if (Auth::user())
			<div class='col-sm-1'><a href="{{route('progress')}}">Update Progress</a></div>
			<div class='col-sm-1'><a href="{{route('logout')}}">Logout</a></div>
			@else
			<div class='col-sm-1'><a href="{{route('login')}}">Login</a></div>
			<div class='col-sm-1'><a href="{{route('register')}}">Register</a></div>
			@endif
		  </div>
		</nav>
		@if (session('status'))
		@foreach (session('status') as $status => $message)
		<div class='alert alert-{{$status}}'>
		  {{$message}}
		</div>
		@endforeach
		@endif
		<h1>@yield ('title')</h1>
		<div id='maincontent'>
		  @yield('content')
		</div>
	  </div>
	</body>
</html>
