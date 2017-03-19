@extends('layout/layout')

@section('title')
{{$faction->name}}
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-12'>
    @if ($faction->player)
    <p>Player faction</p>
    @endif
	<p>Government: {{$faction->government->name}}</p>
  </div>
</div>

<div class='row'>
  <div class='col-sm-6'>
	<h2>Stations</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>System</th><th>Planet</th><th>Type</th></tr>
	  </thead>
	  <tbody>
		@foreach ($faction->stations as $station)
		<tr>
		  <td>{{$station->name}}</td>
		  <td><a href="{{route('systems.show', $station->system->id)}}">{{$station->system->displayName()}}</a></td>
		  <td>{{$station->planet}}</td>
		  <td>{{$station->stationclass->name}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
  <div class='col-sm-6'>
	<h2>Systems</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tbody>
		@foreach ($systems as $system)
		@if ($system->system->inhabited())
		<tr class='
			@if ($system->system->controllingFaction()->id == $faction->id)
		  controlled-system
		  @else
		  uncontrolled-system
		  @endif
			'>
		  <td><a href="{{route('systems.show', $system->system->id)}}">{{$system->system->displayName()}}</a></td>
		  <td>{{number_format($system->influence,1)}}</td>
		  <td>{{$system->state->name}}</td>
		</tr>
		@endif
		@endforeach
	  </tbody>
	</table>
  </div>
</div>

@endsection
