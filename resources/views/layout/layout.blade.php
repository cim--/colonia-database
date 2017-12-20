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

		@if (session('status') && is_array(session('status')))
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
		<p>
		  To discuss this information, volunteer to help collect it, or suggest site improvements, join the <a href="https://discord.gg/fE49mGw">Colonia Census Discord</a>, or contact Ian Doncaster
		</p>
		<p>
		  The database is open source software under the GNU GPL version 3 - <a href='https://github.com/cim--/colonia-database'>source available on Github</a> - <a href='/cdb.sql'>daily database backup</a>
		</p>
		<p>
		  The site is created using data from Elite: Dangerous, with the permission of Frontier Developments plc, for non-commercial purposes. It is not endorsed by nor reflects the views or opinions of Frontier Developments.
		</p>
		<p>
		  The site uses cookies for essential purposes only. No personal data is stored in the cookies unless you use the Login system. The majority of site features do not require a login, and no personal data will be shared with any third party.
		</p>
      </footer>
	</body>
</html>
