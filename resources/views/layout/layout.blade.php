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
      <header>
		@include('layout/navbar')
      </header>
      <div id='main'>

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
      <footer>
		@include('layout/navbar')
		<p>
		  To discuss this information, volunteer to help collect it, or suggest site improvements, use the #colonia-bgs channel on <a href="https://eliteccn.com/welcome/join-ccn/">the CCN Discord</a>, or contact Ian Doncaster
		</p>
		<p>
		  The database is open source software under the GNU GPL version 3 - <a href='https://github.com/cim--/colonia-database'>source available on Github</a> - <a href='/cdb.sql'>daily database backup</a>
		</p>
      </footer>
	</body>
</html>
