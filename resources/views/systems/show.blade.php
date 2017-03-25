@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-12'>
	<p>{{$system->catalogue}}</p>
	@if ($system->inhabited())
	<p>
	  Economy:
	  @include($system->economy->icon)
	  {{$system->economy->name}}
	</p>
	<p>Population: {{$system->population}}</p>
	@else
	<p>Uninhabited system</p>
	@endif
  </div>
</div>

<div class='row'>
@if ($system->inhabited())
  <div class='col-sm-6'>
	<h2>Stations</h2>
	<table class='table table-bordered datatable' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Name</th><th>Planet</th><th>Type</th></tr>
	  </thead>
	  <tbody>
		@foreach ($system->stations as $station)
		<tr class="{{$station->primary ? 'primary-station' : 'secondary-station'}}">
		  <td>{{$station->name}}</td>
		  <td>{{$station->planet}}</td>
		  <td>{{$station->stationclass->name}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	<h2>Factions</h2>
	<table class='table table-bordered datatable' data-order='[[1, "desc"]]' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tfoot>
		<tr>
		  <td colspan='3'>
			@if ($factions->count() > 0)
			Last updated: {{ $factions[0]->displayDate() }}
			@else
			Last updated: never
			@endif
			@if ($userrank > 0)
			<a class='edit' href='{{route('systems.edit', $system->id)}}'>Update</a>
			@endif
		  </td>
		</tr>
	  </tfoot>
	  <tbody>
		@foreach ($factions as $faction)
		<tr class='
			@if ($faction->faction->id == $controlling->id)
		  controlling-faction
		  @else
		  other-faction
		  @endif
			'>
		  <td><a href="{{route('factions.show', $faction->faction->id)}}">{{$faction->faction->name}}</a>
			@include($faction->faction->government->icon)
		  </td>
		  <td>{{number_format($faction->influence, 1)}}</td>
		  <td>
			@include($faction->state->icon)
			{{$faction->state->name}}
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
@endif

  <div class='col-sm-6'>
	<h2>Distances</h2>
	<table class='table table-bordered datatable' data-order='[[1, "asc"]]'>
	  <thead>
		<tr><th>Name</th><th>Distance (LY)</th></tr>
	  </thead>
	  <tbody>
		@foreach ($others as $other)
		<tr class="{{$other->inhabited() ? 'inhabited-system' : 'uninhabited-system'}}">
		  <td>
			<a href="{{route('systems.show', $other->id)}}">
			  {{$other->displayName()}}
			</a>
			@if ($other->inhabited())
			@include($other->economy->icon)
			@include($other->controllingFaction()->government->icon)
			@endif
		  </td>
		  <td>{{$system->distanceTo($other)}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	  
  </div>
</div>

@endsection
