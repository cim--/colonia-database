@extends('layout/layout')

@section('title', 'Colonia Region History Log')

@section('content')

<table class='table table-bordered datatable' data-order='[[0,"desc"]]'>
  <thead>
	<tr><th>Date</th><th>Faction</th><th>Event</th><th>System</th></tr>
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
	  <td>
		@if ($history->expansion)
		expanded to
		@else
		retreated from
		@endif
	  </td>
	  <td>
		@include($history->system->economy->icon)
		<a href='{{route('systems.show', $history->system->id)}}'>
		  {{$history->system->displayName()}}
		</a>
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
