@extends('layout/layout')

@section('title', 'Colonia Region System Database')

@section('content')

<ul id='major-events'>
  @foreach ($importants as $important)
  <li>
	@include($important->faction->government->icon)
	<a href='{{route('factions.show', $important->faction->id)}}'>
	  {{$important->faction->name}}
	</a>
	in
	@include($important->state->icon)
	{{$important->state->name}}
	@if (!in_array($important->state->name, $fakeglobals))
	in
	@include($important->system->economy->icon)
	<a href='{{route('systems.show', $important->system->id)}}'>
	  {{$important->system->displayName()}}
	</a>
	@endif
  </li>
  @endforeach
  @foreach ($historys as $history)
  <li>
	@include($history->faction->government->icon)
	<a href='{{route('factions.show', $history->faction->id)}}'>
	  {{$history->faction->name}}
	</a>
	@if ($history->location_type == 'App\Models\System')
	@if ($history->expansion)
	expanded to
	@else
	retreated from
	@endif
	@include($history->system->economy->icon)
	<a href='{{route('systems.show', $history->system->id)}}'>
	  {{$history->system->displayName()}}
	</a>
	@elseif ($history->location_type == 'App\Models\Station')
	@if ($history->expansion)
	took control of
	@else
	lost control of
	@endif
	@include($history->location->economy->icon)
	<a href='{{route('stations.show', $history->location->id)}}'>
	  {{$history->location->name}}
	</a>
	@endif 
	
  </li>
  @endforeach
</ul>

    
<div class='row'>
  <div class='col-sm-6'>
	<h2><a href="{{route('systems.index')}}">Systems</a></h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Phase</th><th>Name</th><th>Economy</th></tr>
	  </thead>
	  <tbody>
		@foreach ($systems as $system)
		<tr class="{{$system->inhabited() ? 'inhabited-system' : 'uninhabited-system'}}">
		  <td data-sort='{{$system->phase->sequence}}'>{{$system->phase->name}}</td>
		  <td><a href="{{route('systems.show', $system->id)}}">{{$system->displayName()}}</a></td>
		  @if ($system->economy)
          <td>
			@include($system->economy->icon)
			{{$system->economy->name}}
		  </td>
		  @else
		  <td>None</td>
		  @endif
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
  <div class='col-sm-6'>
	<h2><a href="{{route('factions.index')}}">Factions</a></h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>Government</th><th>Player?</th></tr>
	  </thead>
	  <tbody>
		@foreach ($factions as $faction)
		<tr>
		  <td><a href="{{route('factions.show', $faction->id)}}">{{$faction->name}}</a></td>
		  <td>
			@include($faction->government->icon)
			{{$faction->government->name}}
		  </td>
		  <td>{{$faction->player ? 'Yes' : 'No'}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
</div>



@endsection
