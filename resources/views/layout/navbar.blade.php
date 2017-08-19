<nav>
  <ul>
	<li><div>Introduction</div>
	  <ul>
		<li><a href="{{route('index')}}">Home</a></li>
	  </ul>
	</li>
	<li><div>Overview</div>
	  <ul>
		<li><a href="{{route('map')}}">Map</a></li>
		<li><a href="{{route('distances')}}">Distances</a></li>
		<li><a href="{{route('history')}}">History</a></li>
	  </ul>
	</li>
	<li><div>Catalogues</div>
	  <ul>
		<li><a href="{{route('systems.index')}}">Systems</a></li>
		<li><a href="{{route('factions.index')}}">Factions</a></li>
		<li><a href="{{route('stations.index')}}">Stations</a></li>
	  </ul>
	</li>
	<li><div>Reports</div>
	  <ul>
		<li><a href="{{route('reports.traffic')}}">Traffic</a></li>
		<li><a href="{{route('reports.crimes')}}">Crime</a></li>
		<li><a href="{{route('reports.bounties')}}">Bounties</a></li>
	  </ul>
	</li>
	<li><div>Activities</div>
	  <ul>
		<li><a href="{{route('missions.index')}}">Missions</a></li>
		<li><a href="{{route('trade')}}">Trading</a></li>
	  </ul>
	</li>
	<li><div>Contributors</div>
	  <ul>
		<li><a href="{{route('progress')}}">Progress</a></li>
		@if (Auth::user())
		<li><a href="{{route('logout')}}">Logout</a></li>
		@else
		<li><a href="{{route('login')}}">Login</a></li>
		@endif
	  </ul>
	</li>
  </ul>
</nav>
