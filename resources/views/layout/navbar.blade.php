<nav>
  <div class='row'>
	<div class='col-sm-1'><a href="{{route('index')}}">Home</a></div>
    <div class='col-sm-1'><a href="{{route('map')}}">Map</a></div>
	<div class='col-sm-1'><a href="{{route('distances')}}">Distances</a></div>
	<div class='col-sm-1'><a href="{{route('history')}}">History</a></div>
    <div class='col-sm-1'><a href="{{route('reports')}}">Reports</a></div>
    <div class='col-sm-1'><a href="{{route('systems.index')}}">Systems</a></div>
	<div class='col-sm-1'><a href="{{route('factions.index')}}">Factions</a></div>
	<div class='col-sm-1'><a href="{{route('stations.index')}}">Stations</a></div>
    <div class='col-sm-1'><a href="{{route('missions.index')}}">Missions</a></div>
    <div class='col-sm-1'></div>
	@if (Auth::user())
	<div class='col-sm-1'><a href="{{route('progress')}}">Update Progress</a></div>
	<div class='col-sm-1'><a href="{{route('logout')}}">Logout</a></div>
	@else
    <div class='col-sm-1'></div>
	<div class='col-sm-1'><a href="{{route('login')}}">Login</a></div>
	@endif
  </div>
</nav>
