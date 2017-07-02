@extends('layout/layout')

@section('title')
{{$faction->name}}
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-12 faction-properties'>
    @if ($faction->player)
    <p>Player faction</p>
    @endif
	<p><span class='faction-property'>Government</span>: 
	  @include($faction->government->icon)
	  {{$faction->government->name}}
	</p>
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
	@if ($faction->eddb)
	<p><a href='https://eddb.io/faction/{{$faction->eddb}}'>EDDB Record</a></p>
	@endif
  </div>
</div>

<div class='row'>
  <div class='col-sm-6'>
	<h2>Systems</h2>
	<p><a href='{{route("factions.showhistory", $faction->id)}}'>Influence history</a></p>
	<table class='table table-bordered datatable' data-page-length='25'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
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
			@include($system->state->icon)
			{{$system->state->name}}
		  </td>
		</tr>
		@endif
		@endforeach
	  </tbody>
	</table>
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

	<h2>State History</h2>
	@include('layout/chart')
  </div>
</div>


@endsection
