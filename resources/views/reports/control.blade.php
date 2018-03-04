@extends('layout/layout')

@section('title', 'Control Report')

@section('content')

<table class='table table-bordered datatable' data-page-length='25' data-order='[[3, "desc"], [4, "desc"], [2,"desc"]]'>
  <thead>
	<tr>
	  <th>Faction</th>
	  <th>Home System</th>
	  <th>Systems Present</th>
	  <th>Systems Controlled</th>
	  <th>Stations Controlled</th>
	  <th>Orbitals</th>
	  <th>Planet Bases</th>
	  <th>Settlements</th>
      <th title="Control of stations exporting trades goods">Production Control</th>
      <th title="Control of strategic assets">Strategic Assets</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($factions as $faction)
	<tr>
	  <td>
		<a href="{{route('factions.show', $faction->id)}}">
		  {{$faction->name}}
		</a>
		@include($faction->government->icon)
	  </td>
	  <td>
		@if ($faction->system)
		<a href="{{route('systems.show', $faction->system_id)}}">
		  {{$faction->system->displayName()}}
		</a>
		@include($faction->system->economy->icon)
		@else
???
		@endif
	  </td>
	  <td>{{$faction->influences->count()}}</td>
	  <td>{{$faction->stations->where('primary', 1)->count()}}</td>
	  <td>{{$faction->stations->count()}}</td>
	  <td>{{$faction->stations->where('gravity', null)->count()}}</td>
	  <td>
		{{$faction->stations->where('gravity', '>', 0)
		->filter(function($s) {
		return $s->stationclass->hasSmall;
		})
		->count()}}
	  </td>
	  <td>
		{{$faction->stations->where('gravity', '>', 0)
		->filter(function($s) {
		return !$s->stationclass->hasSmall;
		})
		->count()}}
	  </td>
	  <td>
		{{$faction->stations->filter(function($s) {
		return $s->stationclass->hasSmall;
		})
		->filter(function($s) {
		return in_array($s->economy->name, ["Extraction", "Refinery", "Industrial", "High-Tech", "Agricultural", "Military"]);
		})
		->count()}}
	  </td>
	  <td>
		{{$faction->stations->filter(function($s) {
		return $s->strategic;
		})
		->count()}}
	  </td>
	  </tr>
	@endforeach
  </tbody>
</table>

@endsection
