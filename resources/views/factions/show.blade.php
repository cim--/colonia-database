@extends('layout/layout')

@section('title')
{{$faction->name}}
@endsection

@section('content')
@include('components/trackbox', ['domain' => 'factions', 'id' => $faction->id])
<div class='row'>
  <div class='col-sm-12 faction-properties'>
    @if ($faction->player)
    <p>Player faction</p>
    @endif

	@if ($faction->government->name == "Engineer" && $faction->engineers->count() > 0)
	<p>This is an Engineer faction supporting <a href='{{route('engineers.show', $faction->engineers[0]->id)}}'>{{$faction->engineers[0]->name}}</a>.</p>
	@endif
	
	<p><span class='faction-property'>Government</span>: 
	  @include($faction->government->icon)
	  {{$faction->government->name}}
	  @if ($faction->ethos)
	  ({{$faction->ethos->name}} ethos)
	  @endif
	</p>
	@if (!$faction->virtual)
	<p><span class='faction-property'>Pending States</span>:
	  @if (count($faction->states) > 0)
	  @foreach ($faction->states as $state)
	  <span class='pending-state'>
		@include($state->icon)
		{{$state->name}}
	  </span>
	  @endforeach
	  @else
	  Unknown
	  @endif

	  @if ($userrank > 0)
	  <a class='edit' href='{{route('factions.edit', $faction->id)}}'>Update</a>
	  @endif
	</p>
	@else
	@if ($userrank > 1)
	<a class='edit' href='{{route('factions.edit', $faction->id)}}'>Update</a>
	@endif
	@endif
	@if ($faction->eddb)
	<p><a href='https://eddb.io/faction/{{$faction->eddb}}'>EDDB Record</a></p>
	@endif
    @if ($faction->system_id)
	<p><span class='faction-property'>Home system</span>:
	  <a href="{{route('systems.show', $faction->system->id)}}">
		{{$faction->system->displayName()}}
	  </a>
	</p>
	@endif
  </div>
</div>

<div class='row'>
  <div class='col-sm-6'>
	<h2>Systems</h2>
	@if (!$faction->virtual)
	<p><a href='{{route("factions.showhistory", $faction->id)}}'>Influence history</a></p>
	<table class='table table-bordered datatable' data-page-length='25'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th><th>Mood</th><th>Rank</th></tr>
	  </thead>
	  <tbody>
		@foreach ($systems as $system)
		@if ($system->system && $system->system->inhabited())
		<tr class='
			@if ($system->system->controllingFaction()->id == $faction->id)
		  controlled-system
		  @else
		  uncontrolled-system
		  @endif
			'>
		  <td><a href="{{route('systems.show', $system->system->id)}}">{{$system->system->displayName()}}</a>
			@include($system->system->economy->icon)
		  </td>
		  <td>{{number_format($system->influence,1)}}</td>
		  <td>
			@foreach ($system->states as $state)
			@include($state->icon)
			{{$state->name}}
			@endforeach
		  </td>
		  <td>
			@include('icons/happiness', ['happiness' => $system->happiness, 'label'=>true])
		  </td>
		  <td>
			{{$faction->currentRankString($system->system)}}
		  </td>
		</tr>
		@endif
		@endforeach
	  </tbody>
	</table>
	@else
	<table class='table table-bordered datatable' data-page-length='25'>
	  <thead>
		<tr><th>Name</th><th>Control?</th></tr>
	  </thead>
	  <tbody>
		@foreach ($faction->stations as $station)
		<tr class='
			@if ($station->system->controllingFaction()->id == $faction->id)
		  controlled-system
		  @else
		  uncontrolled-system
		  @endif
			'>
		  <td><a href="{{route('systems.show', $station->system->id)}}">{{$station->system->displayName()}}</a>
			@include($station->system->economy->icon)
		  </td>
		  <td>
			@if ($station->system->controllingFaction()->id == $faction->id)
			Yes
			@else
			No
			@endif
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	@endif
  </div>
  <div class='col-sm-6'>
	<h2>Stations</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>System</th><th>Planet</th><th>Type</th></tr>
	  </thead>
	  <tbody>
		@foreach ($faction->stations as $station)
		<tr>
		  <td>
			<a href='{{route('stations.show', $station->id)}}'>{{$station->name}}</a>
			@include($station->economy->icon)
		  </td>
		  <td><a href="{{route('systems.show', $station->system->id)}}">{{$station->system->displayName()}}</a>
		  </td>
		  <td>{{$station->planet}}</td>
		  <td>{{$station->stationclass->name}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	@if (!$faction->virtual)
	<h2>State History</h2>
	@include('layout/chart')
	@endif
  </div>
</div>


@endsection
