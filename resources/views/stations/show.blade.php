@extends('layout/layout')

@section('headtitle')
{{$station->name}}
@endsection

@section('title')
{{$station->name}}
@if ($station->strategic)
@include('icons/misc/strategic')
@endif
@endsection

@section('content')

@if ($userrank > 0)
<a class='edit' href='{{route('stations.edit', $station->id)}}'>Update</a>
@endif

@if ($station->strategic)
<p>Due to its unique resources, this station is designated a strategic asset for the Colonia region.</p>
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
	<th>Gravity</th>
	<td>
	  @if ($station->gravity)
	  {{$station->gravity}}G
	  @else
	  Orbital
	  @endif
	</td>
  </tr>
  <tr>
	<th>Docking Pads</th>
	<td>
	  @if ($station->stationclass->hasSmall) Small @endif
	  @if ($station->stationclass->hasMedium) Medium @endif
	  @if ($station->stationclass->hasLarge) Large @endif
	</td>
  </tr>
  <tr>
	<th>Facilities</th>
	<td>
	  @foreach ($station->facilities->sortBy('name') as $facility)
	  @if (!$facility->pivot->enabled)<span class='facility-disabled'>@endif
		@include ($facility->icon)
		@if ($facility->name == "Commodities")
		<a href="{{route('stations.showtrade', $station->id)}}">
		  {{$facility->name}}
		</a>
		(economy size:
		@if ($station->economysize)
		{{number_format($station->economysize)}})
		@else
		Unknown)
		@endif
		@elseif ($facility->name == "Outfitting")
		<a href="{{route('stations.showoutfitting', $station->id)}}">
		  {{$facility->name}}
		</a>
		@elseif ($facility->name == "Shipyard")
		<a href="{{route('stations.showshipyard', $station->id)}}">
		  {{$facility->name}}
		</a>
		@else
		{{$facility->name}}
		@endif
	  @if (!$facility->pivot->enabled)</span>@endif
	  <br>
	  @endforeach
	</td>
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
	  <a href='{{route('factions.show', $station->faction->id)}}'>{{$station->faction->name}}</a>
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

@if ($station->eddb)
<p><a href='https://eddb.io/station/{{$station->eddb}}'>EDDB Record</a></p>
@endif
    

@endsection
