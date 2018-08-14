@extends('layout/layout')

@section('title')
Stations
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('stations.create')}}'>New station</a></p>
@endif
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>Type</th>
	  <th>Location</th>
	  <th>Distance (Ls)</th>
      <th>Gravity</th>
      <th>Docking</th>
      <th>Facilities</th>
	  <th>Economy</th>
      <th>Economy Size</th>
	  <th>Controlling Faction</th>
      <th>Primary?</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($stations as $station)
	<tr>
	  @if ($station->strategic)
	  <td data-search="{{$station->name}} Strategic">
		<a href='{{route("stations.show", $station->id)}}'>{{$station->name}}</a>
		@include('icons/misc/strategic')
	  </td>
	  @else
	  <td data-search="{{$station->name}}">
		<a href='{{route("stations.show", $station->id)}}'>{{$station->name}}</a>
	  </td>
	  @endif
	  <td>{{$station->stationclass->name}}</td>
	  <td><a href='{{route("systems.show", $station->system->id)}}'>{{$station->system->displayName()}}</a> {{$station->planet}}</td>
	  <td>{{$station->distance}}</td>
	  <td>
		@if ($station->gravity)
		{{$station->gravity}}G
		@else
		Orbital
		@endif
	  </td>
	  <td data-search='
		@if ($station->stationclass->hasSmall) Small Pad @endif
		@if ($station->stationclass->hasMedium) Medium Pad @endif
		@if ($station->stationclass->hasLarge) Large Pad @endif
		  '>
		@if ($station->stationclass->hasSmall) S @endif
		@if ($station->stationclass->hasMedium) M @endif
		@if ($station->stationclass->hasLarge) L @endif
	  </td>
	  <td data-search='
		@foreach ($station->facilities as $facility)
{{$facility->name}}
@endforeach
'>
		@foreach ($station->facilities->sortBy('name') as $facility)
		@if (!$facility->pivot->enabled)<span class='facility-disabled'>@endif
		  @include ($facility->icon)
		  @if (!$facility->pivot->enabled)</span>@endif
		@endforeach
	  </td>
	  <td>
		@include($station->economy->icon)
		{{$station->economy->name}}
	  </td>
	  <td>
		@if ($station->economysize)
		{{number_format($station->displayEconomysize())}}
		@endif
	  </td>
	  <td data-search="{{$station->faction->government->name}} {{$station->faction->name}}">
		@include($station->faction->government->icon)
		@if ($station->faction->currentState($station->system))
		@include ($station->faction->currentState($station->system)->icon)
		@endif

		<a href="{{route('factions.show', $station->faction->id)}}">{{$station->faction->name}}</a>
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
