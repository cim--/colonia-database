@extends('layout/layout')

@section('title', 'Colonia Region History Log')

@section('content')

<table class='table table-bordered datatable' data-order='[[0,"desc"]]'>
  <thead>
	<tr><th>Date</th><th>Faction</th><th>Event</th><th>Location</th></tr>
  </thead>
  <tbody>
	@foreach ($historys as $history)
	<tr>
	  <td data-sort='{{$history->date->format('Y-m-d')}}'>{{\App\Util::displayDate($history->date)}}</td>
	  <td>
		@include($history->faction->government->icon)
		<a href='{{route('factions.show', $history->faction->id)}}'>
		  {{$history->faction->name}}
		</a>
	  </td>
	  @if ($history->location_type == 'App\Models\System')
	  <td>
		@if ($history->expansion)
		expanded to
		@else
		retreated from
		@endif
	  </td>
	  <td>
		@include($history->location->economy->icon)
		<a href='{{route('systems.show', $history->location->id)}}'>
		  {{$history->location->displayName()}}
		</a>
	  </td>
	  @elseif ($history->location_type == 'App\Models\Station')
	  <td>
		@if ($history->expansion)
		took control of
		@else
		lost control of
		@endif
	  </td>
	  <td>
		@include($history->location->economy->icon)
		<a href='{{route('stations.show', $history->location->id)}}'>
		  {{$history->location->name}}
		</a>
	  </td>
	  @endif 
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
