<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

		<title>
		  @section('headtitle')
		  @yield ('title')
		  @show
		</title>
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
		  To discuss this information, volunteer to help collect it, or suggest site improvements, join the <a href="https://discord.gg/fE49mGw">Colonia Census Discord</a>, <a href="https://forums.frontier.co.uk/showthread.php/398547-The-Colonia-Census-history-and-geography-resource-for-the-Colonia-region">post in the forum thread</a>, or contact Ian Doncaster
		</p>
		<p>
		  The database is open source software under the GNU GPL version 3 - <a href='https://github.com/cim--/colonia-database'>source available on Github</a> - <a href='/cdb.sql.gz'>daily database backup</a>
		</p>
		<p>
		  The site is created using data from <a href='https://www.elitedangerous.com/'>Elite: Dangerous</a>, with the permission of Frontier Developments plc, for non-commercial purposes. It is not endorsed by nor reflects the views or opinions of Frontier Developments and no employee of Frontier Developments was involved in the making of it.
		</p>
		<p>
		  The site uses cookies for essential purposes only. No personal data is stored in the cookies unless you use the Login system. The majority of site features do not require a login, and no personal data will be shared with any third party. If you use the Discord Bot, please see the additional <a href='{{route('intro.about')}}#bpp'>bot privacy policy</a>.
		</p>
      </footer>
	</body>
</html>
