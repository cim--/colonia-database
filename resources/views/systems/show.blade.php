@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@endsection

@section('content')

@include('components/trackbox', ['domain' => 'systems', 'id' => $system->id])
<div class='row'>
  <div class='col-sm-6 system-properties'>
	@if ($system->name)
	<span class='system-catalogue'>({{$system->catalogue}})</span>
	@endif
	@if ($system->inhabited())
	<p>
	  <span class='system-property'>Economy</span>:
	  @include($system->economy->icon)
	  {{$system->economy->name}}
	</p>
	<div class='row'>
	  <div class='col-sm-6'>
          <p><span class='system-property'>Population</span>: {{number_format($system->population)}}</p>
	  </div>
	  <div class='col-sm-6'>
        <p><span class='system-property'>Exploration Value</span>: {{$system->explorationvalue ? number_format($system->explorationvalue) : "not known"}}</p>
	  </div>
	</div>
	<p><span class='system-property'>Security</span>: {{$system->security}}</p>
	@if (!$system->virtualonly)
	<div class='row'>
	  <div class='col-sm-6'>
		@if ($report && $report->system_id == $system->id)
		<p><span class='system-property'>Traffic Report</span>: {{$report->traffic}}</p>
		<p><span class='system-property'>Crime Report</span>: {{number_format($report->crime)}}</p>
		<p><span class='system-property'>Bounty Report</span>: {{number_format($report->bounties)}}</p>
	  </div>
	  <div class='col-sm-6'>
		<p><a href='#reporthistory'>Reports history</a></p>
		<p>Last update: <span title='{{$report->created_at->format("H:i")}}'>{{\App\Util::displayDate($report->date)}}</span>
		  @else
		<p><span class='system-property'>Traffic Report</span>: ?</p>
		<p><span class='system-property'>Crime Report</span>: ?</p>
		<p><span class='system-property'>Bounty Report</span>: ?</p>
	  </div>
	  <div class='col-sm-6'>
		<p>Last update: never
		  @endif
		  @if ($userrank > 0)
		  <a class='edit' href='{{route('systems.editreport', $system->id)}}'>Update</a>
		  @endif
		</p>
	  </div>
	</div>
	@endif
	@else
	<p>Uninhabited system
	  @if ($userrank > 0)
	  <a class='edit' href='{{route('systems.edit', $system->id)}}'>Update</a>
	  @endif
    </p>
    <p><span class='system-property'>Exploration Value</span>: {{$system->explorationvalue ? number_format($system->explorationvalue) : "not known"}}</p>
	@endif
	<p>
	  @foreach ($system->facilities->sortBy('name') as $facility)
	  @if (!$facility->pivot->enabled)<span class='facility-disabled'>@endif
		@include ($facility->icon)
		{{$facility->name}}@if (!$loop->last || $system->cfthmc),@endif
		@if (!$facility->pivot->enabled)</span>@endif
	  @endforeach
	  @if ($system->cfthmc)
	  @include('icons/facilities/systems/terraformable')
	  {{$system->cfthmc}} lifeless
      @if ($system->cfthmc > 1)
      terraformables
	  @else
	  terraformable
	  @endif
	  @endif
	</p>

	@if ($system->eddb)
	<p><a href='https://eddb.io/system/{{$system->eddb}}'>EDDB Record</a></p>
	@endif
	@if ($system->edsm)
	<p><a href='https://www.edsm.net/en/system/id/{{$system->edsm}}/name/{{urlencode($system->displayName())}}'>EDSM Record</a></p>
	@endif
	
  </div>
  <div class='col-sm-6'>
	<table class='table'>
	  <tr>
		<td></td>
		<th>X</th>
		<th>Y</th>
		<th>Z</th>
	  </tr>
	  <tr>
		<td>Colonial</td>
		<td>{{number_format($colcoords->x, 5)}}</td>
		<td>{{number_format($colcoords->y, 5)}}</td>
		<td>{{number_format($colcoords->z, 5)}}</td>
	  </tr>
	  <tr>
		<td>Traditional</td>
		<td>{{number_format($system->x, 5)}}</td>
		<td>{{number_format($system->y, 5)}}</td>
		<td>{{number_format($system->z, 5)}}</td>
	  </tr>
	  </table>
  </div>
</div>

<div class='row'>
@if ($system->inhabited())
  <div class='col-sm-6'>
	<h2>Stations</h2>
	<table class='table table-bordered datatable' data-paging='false' data-searching='false' data-info='false'>
	  <thead>
		<tr><th>Name</th><th>Planet</th><th>Type</th><th>Controller</th></tr>
	  </thead>
	  <tbody>
		@foreach ($system->stations as $station)
		<tr class="{{$station->primary ? 'primary-station' : 'secondary-station'}}">
		  <td><a href='{{route('stations.show', $station->id)}}'>{{$station->name}}</a>
			@include($station->economy->icon)
		  </td>
		  <td>{{$station->planet}}</td>
		  <td>{{$station->stationclass->name}}</td>
		  <td>
			@include($station->faction->government->icon)
			<a href='{{route('factions.show', $station->faction_id)}}'>{{$station->faction->name}}</a>
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>

	@if ($system->megashiproutes->count() > 0)
	<h2>Megaships</h2>
	<table class='table table-bordered datatable' data-paging='false' data-searching='false' data-info='false' data-order='[[2, "asc"],[1, "asc"]]'>
	  <thead>
		<tr><th>Class</th><th>Ship</th><th>Next Arrival</th><th>Next Departure</th>
	  </thead>
	  <tbody>
		@foreach ($system->megashiproutes as $route)
		<tr>
		  <td>
			@include($route->megaship->megashipclass->icon)
			{{$route->megaship->megashipclass->name}}
		  </td>
		  <td>
			<a href='{{route('megaships.show', $route->megaship_id)}}'>
			  {{$route->megaship->serial}}
			</a>
		  </td>
		  <td data-sort='{{$route->nextArrival() ? $route->nextArrival()->format("Y-m-d") : 0}}'>
			@if ($route->nextArrival())
			{{App\Util::displayDate($route->nextArrival())}}
			@else
			<strong>Present</strong>
			@endif
		  </td>
		  <td data-sort='{{$route->nextDeparture()->format("Y-m-d")}}'>
			@if ($route->megaship->megashipclass->operational)
			{{App\Util::displayDate($route->nextDeparture())}}
			@else
			<strong>Not operational</strong>
			@endif
		  </td>
		@endforeach
	  </tbody>
	</table>
	@endif

	@if ($system->installations->count() > 0)
	<h2>Installations</h2>
	<table class='table table-bordered datatable' data-paging='false' data-searching='false' data-info='false' data-order='[[1, "asc"]]'>
	  <thead>
		<tr><th>Class</th><th>Planet</th><th>Owner</th>
	  </thead>
	  <tbody>
		@foreach ($system->installations as $installation)
		<tr>
		  <td>
			@include($installation->installationclass->icon)
			<a href='{{route('installations.show', $installation->id)}}'>
			  {{$installation->installationclass->name}}
			  @if ($installation->name)
			  ({{$installation->name}})
			@endif
			</a>
		  </td>
		  <td>
			{{$installation->planet}}
		  </td>
		  <td>
		    @if ($installation->faction_id)
		    @include ($installation->faction->government->icon)
		    <a href='{{route('factions.show', $installation->faction_id)}}'>
		      {{$installation->faction->name}}
		    </a>
		    @else
		    Not Known
		    @endif
		  </td>
		@endforeach
	  </tbody>
	</table>
	@endif
	
	<h2>Factions</h2>
	@if (!$system->virtualonly)
	@if ($system->bgslock)
	<p>Political activity in this system is restricted.</p>
	@endif
	<p>
	  <a href='{{route("systems.showhistory", $system->id)}}'>Influence history</a>,
	  <a href='{{route("systems.showhappiness", $system->id)}}'>Happiness history</a>
	</p>

	<table class='table table-bordered datatable' data-order='[[1, "desc"]]' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>States</th><th>Mood</th></tr>
	  </thead>
	  <tfoot>
		<tr>
		  <td colspan='4'>
			@if ($factions->count() > 0)
			Last updated: <span title='{{$factions[0]->created_at->format("H:i")}}'>{{ $factions[0]->displayDate() }}</span>
			@else
			Last updated: never
			@endif
			@if ($userrank > 0)
			<a class='edit' href='{{route('systems.edit', $system->id)}}'>Update</a>
			@endif
		  </td>
		</tr>
	  </tfoot>
	  <tbody>
		@foreach ($factions as $faction)
		<tr class='
			@if ($faction->faction->id == $controlling->id)
		  controlling-faction
		  @else
		  other-faction
		  @endif
			'>
		  <td><a href="{{route('factions.show', $faction->faction->id)}}">{{$faction->faction->name}}</a>
			@include($faction->faction->government->icon)
                    @if ($faction->faction->system_id == $system->id)
		    @include('icons/misc/homesystem')
		    @endif
		  </td>
		  <td>{{number_format($faction->influence, 1)}}</td>
		  <td>
			@foreach($faction->states as $state)
			@include($state->icon)
			{{$state->name}}
			@endforeach
		  </td>
		  <td>
			@include('icons/happiness', ['happiness' => $faction->happiness, 'label'=>true])
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>

	@if ($conflicts->count() > 0)
	<h2>Conflicts</h2>
	<table class='table table-bordered datatable' data-paging='false' data-order='[[1, "asc"]]' data-searching='false' data-info='false'>
	  <thead>
	    <tr>
	      <th>State</th><th>Faction 1</th><th>Score</th><th>Faction 2</th>
	    </tr>
	  </thead>
	  <tbody>
	    @foreach ($conflicts as $conflict)
	    <tr
	       @if (($conflict->asset1 && $conflict->asset1->isController()) || ($conflict->asset2 && $conflict->asset2->isController()))
	      class='controlconflict'
	      @endif
	      >
	      <td>{{ucwords($conflict->status)}} {{ucwords($conflict->type)}}</td>
	      <td>
		@include($conflict->faction1->government->icon)
		<a href='{{route('factions.show', $conflict->faction1->id)}}'>
		  {{$conflict->faction1->name}}
		</a>
		@if ($conflict->asset1)
		(<a href='{{route($conflict->asset1->displayRoute(), $conflict->asset1->id)}}'>{{$conflict->asset1->displayName()}}</a>)
		@endif
	      </td>
	      <td>{{$conflict->score}}</td>
	      <td>
		@include($conflict->faction2->government->icon)
		<a href='{{route('factions.show', $conflict->faction2->id)}}'>
		  {{$conflict->faction2->name}}
		</a>
		@if ($conflict->asset2)
		(<a href='{{route($conflict->asset2->displayRoute(), $conflict->asset2->id)}}'>{{$conflict->asset2->displayName()}}</a>)
		@endif
	      </td>
	    </tr>
	    @endforeach
	  </tbody>
	</table>
	@endif
	
	@else
	<p>
	  System administrated by <a href="{{route('factions.show', $controlling->id)}}">{{$controlling->name}}</a>
	  @include($controlling->government->icon)
	</p>
	@if ($userrank > 1)
	<a class='edit' href='{{route('systems.edit', $system->id)}}'>Update</a>
	@endif
	@endif
  </div>
@endif

  <div class='col-sm-6'>

	@if ($system->sites->count() > 0)
	<h2>Sites</h2>
	<table class='table table-bordered datatable' data-order='[[1, "asc"]]' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Planet</th><th>Location</th><th>Site</th></tr>
	  </thead>
	  <tbody>
		@foreach ($system->sites as $site)
		<tr>
		  <td>{{$site->planet}}</td>
		  <td>
			@if ($site->coordinates)
			{{$site->coordinates}}
			@else
			Orbit
			@endif
		  </td>
		  <td>
			{{$site->sitecategory->name}}: <a href='{{route('sites.show', $site->id)}}'>{{$site->summary}}</a>
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	@endif
	
	<h2>Distances</h2>
	<table class='table table-bordered datatable' data-order='[[1, "asc"]]'>
	  <thead>
		<tr><th>Name</th><th>Distance (LY)</th></tr>
	  </thead>
	  <tbody>
		@foreach ($others as $other)
		<tr class="{{$other->inhabited() ? 'inhabited-system' : 'uninhabited-system'}} {{$system->distanceTo($other) <= 20 ? 'near-system' : 'far-system'}}">
		  <td>
			<a href="{{route('systems.show', $other->id)}}">
			  {{$other->displayName()}}
			</a>
			@if ($other->inhabited())
			@include($other->economy->icon)
			@include($other->controllingFaction()->government->icon)
			@endif
		  </td>
		  <td>{{number_format($system->distanceTo($other), 2)}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	  
  </div>
</div>
@if ($system->inhabited() && !$system->virtualonly)
<h2>Report history</h2>
@include('layout/chart')
@endif

@endsection
