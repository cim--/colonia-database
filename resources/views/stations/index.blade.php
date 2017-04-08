@extends('layout/layout')

@section('title')
Stations
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('stations.create')}}'>New station</a></p>
@endif
    
<table class='table table-bordered datatable' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>Type</th>
	  <th>Location</th>
	  <th>Distance (Ls)</th>
      <th>Facilities</th>
	  <th>Economy</th>
	  <th>Controlling Faction</th>
      <th>Primary?</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($stations as $station)
	<tr>
	  <td><a href='{{route("stations.show", $station->id)}}'>{{$station->name}}</a></td>
	  <td>{{$station->stationclass->name}}</td>
	  <td><a href='{{route("systems.show", $station->system->id)}}'>{{$station->system->displayName()}}</a> {{$station->planet}}</td>
	  <td>{{$station->distance}}</td>
	  <td>
		@foreach ($station->facilities as $facility)
		@include ($facility->icon)
		@endforeach
	  </td>
	  <td>
		@include($station->economy->icon)
		{{$station->economy->name}}
	  </td>
	  <td>
		@include($station->faction->government->icon)
		{{$station->faction->name}}
	  </td>
	  <td>
		@if ($station->primary)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	  
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
