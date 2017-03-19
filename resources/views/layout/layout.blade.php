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
		<h1>@yield ('title')</h1>
		<div id='maincontent'>
		  @yield('content')
		</div>
	  </div>
	</body>
</html>
