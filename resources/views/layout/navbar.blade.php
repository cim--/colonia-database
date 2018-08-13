<nav>
  <ul>
	<li><div>Introduction</div>
	  <ul >
		<li><a href="{{route('index')}}">Home</a></li>
		<li><a title='About the Census' href="{{route('intro.about')}}">Census</a></li>
		<li><a title='The story of Colonia' href="{{route('intro.story')}}">Story</a></li>
		<li><a title='Differences between Colonia and Sol' href="{{route('intro.new')}}">Differences</a></li>
		<li><a title='Comparing Colonia to other settlements' href="{{route('intro.regions')}}">Regions</a></li>
     	<li><a href="{{route('intro.icons')}}">Icon Key</a></li>
	  </ul>
	</li>
	<li><div>Overview</div>
	  <ul>
		<li><a href="{{route('map')}}">Map</a></li>
		<li><a href="{{route('distances')}}">Distances</a></li>
		<li><a href="{{route('history')}}">History</a></li>
     	<li><a href="{{route('history.trends')}}">Activity</a></li>
	  </ul>
	</li>
	<li><div>Catalogues</div>
	  <ul >
		<li><a href="{{route('systems.index')}}">Systems</a></li>
		<li><a href="{{route('factions.index')}}">Factions</a></li>
		<li><a href="{{route('stations.index')}}">Stations</a></li>
     	<li><a href="{{route('megaships.index')}}">Megaships</a></li>
        <li><a href="{{route('installations.index')}}">Installations</a></li>
        <li><a href="{{route('sites.index')}}">Sites</a></li>
	  </ul>
	</li>
	<li><div>Reports</div>
	  <ul >
		<li><a href="{{route('reports.traffic')}}">Traffic</a></li>
		<li><a href="{{route('reports.crimes')}}">Crime</a></li>
		<li><a href="{{route('reports.bounties')}}">Bounties</a></li>
        <li><a href="{{route('reports.control')}}">Control</a></li>
     	<li><a href="{{route('reports.reach')}}">Reach</a></li>
		<li><a href="{{route('reports.states')}}">States</a></li>
	  </ul>
	</li>
	<li><div>Economy</div>
	  <ul class='compactnav'>
		<li><a href="{{route('trade')}}">Trading</a></li>
		<li><a href="{{route('reserves')}}">Reserves</a></li>
     	<li><a href="{{route('effects')}}">Effects</a></li>
        <li><a href="{{route('specialisation')}}">Specialities</a></li>
     	<li><a href="{{route('outfitting')}}">Outfitting</a></li>
		<li><a href="{{route('outfitting.shipyard')}}">Shipyard</a></li>
		<li><a href="{{route('missions.index')}}">Missions</a></li>
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
