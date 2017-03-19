@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-12'>
	<p>{{$system->catalogue}}</p>
	@if ($system->inhabited())
	<p>Economy: {{$system->economy->name}}</p>
	<p>Population: {{$system->population}}</p>
	@else
	<p>Uninhabited system</p>
	@endif
  </div>
</div>

@if ($system->inhabited())
<div class='row'>
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
  </div>
  <div class='col-sm-6'>
	<h2>Factions</h2>
	<table class='table table-bordered datatable' data-order='[[1, "desc"]]' data-paging='false' data-searching='false'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tfoot>
		<tr>
		  <td colspan='3'>
			Last updated: {{ $factions[0]->displayDate() }}
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
		  <td><a href="{{route('factions.show', $faction->faction->id)}}">{{$faction->faction->name}}</a></td>
		  <td>{{number_format($faction->influence, 1)}}</td>
		  <td>{{$faction->state->name}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
</div>
@endif

<div class='row'>
  <div class='col-sm-6'>
	<h2>Distances</h2>
	<table class='table table-bordered datatable' data-order='[[1, "asc"]]'>
	  <thead>
		<tr><th>Name</th><th>Distance (LY)</th></tr>
	  </thead>
	  <tbody>
		@foreach ($others as $other)
		<tr class="{{$other->inhabited() ? 'inhabited-system' : 'uninhabited-system'}}">
		  <td><a href="{{route('systems.show', $other->id)}}">{{$other->displayName()}}</a></td>
		  <td>{{$system->distanceTo($other)}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
	  
  </div>
</div>

@endsection
