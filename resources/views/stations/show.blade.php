@extends('layout/layout')

@section('title')
{{$station->name}}
@endsection

@section('content')

@if ($userrank > 0)
<a class='edit' href='{{route('stations.edit', $station->id)}}'>Update</a>
@endif


<table class='table table-bordered'>
  <tr>
	<th>Type</th>
	<td>{{$station->stationclass->name}}</td>
  </tr>
  <tr>
	<th>Location</th>
	<td><a href='{{route("systems.show", $station->system->id)}}'>{{$station->system->displayName()}}</a> {{$station->planet}}</td>
  </tr>
  <tr>
	<th>Distance (Ls)</th>
	<td>{{$station->distance}}</td>
  </tr>
  <tr>
	<th>Economy</th>
	<td>
	  @include($station->economy->icon)
	  {{$station->economy->name}}
	</td>

  </tr>
  <tr>
	<th>Controlling Faction</th>
	<td>
	  @include($station->faction->government->icon)
	  {{$station->faction->name}}
	</td>

  </tr>
  <tr>
    <th>Primary?</th>
	<td>
	  @if ($station->primary)
	  @include('layout/yes')
	  @else
	  @include('layout/no')
	  @endif
	</td>
  </tr>
</table>

    

@endsection
