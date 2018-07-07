@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@endsection

@section('content')

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
		{{$facility->name}}@if (!$loop->last),@endif
		@if (!$facility->pivot->enabled)</span>@endif
	  @endforeach
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
		<tr><th>Name</th><th>Planet</th><th>Type</th></tr>
	  </thead>
	  <tbody>
		@foreach ($system->stations as $station)
		<tr class="{{$station->primary ? 'primary-station' : 'secondary-station'}}">
		  <td><a href='{{route('stations.show', $station->id)}}'>{{$station->name}}</a>
			@include($station->economy->icon)
		  </td>
		  <td>{{$station->planet}}</td>
		  <td>{{$station->stationclass->name}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>

	@if ($system->megashiproutes->count() > 0)
	<h2>Megaships</h2>
	<table class='table table-bordered datatable' data-paging='false' data-searching='false' data-info='false' data-order='[[1, "asc"]]'>
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
		  <td>
			@if ($route->nextArrival())
			{{App\Util::displayDate($route->nextArrival())}}
			@else
			<strong>Present</strong>
			@endif
		  </td>
		  <td>
			{{App\Util::displayDate($route->nextDeparture())}}
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
	<p><a href='{{route("systems.showhistory", $system->id)}}'>Influence history</a></p>

	<table class='table table-bordered datatable' data-order='[[1, "desc"]]' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tfoot>
		<tr>
		  <td colspan='3'>
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
		  </td>
		  <td>{{number_format($faction->influence, 1)}}</td>
		  <td>
			@include($faction->state->icon)
			{{$faction->state->name}}
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>
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
	<h2>Distances</h2>
	<table class='table table-bordered datatable' data-order='[[1, "asc"]]'>
	  <thead>
		<tr><th>Name</th><th>Distance (LY)</th></tr>
	  </thead>
	  <tbody>
		@foreach ($others as $other)
		<tr class="{{$other->inhabited() ? 'inhabited-system' : 'uninhabited-system'}}">
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
