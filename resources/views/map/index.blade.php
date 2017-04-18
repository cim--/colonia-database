@extends('layout/layout')

@section('title', 'System Map')

@section('content')

<p>
  <strong>Projection</strong>: <select id='mapctrlprojection' name='projection'>
	<option selected='selected'>XZ</option>
	<option>XY</option>
	<option>ZY</option>
  </select> ;
  <strong>Links</strong>: 15 LY ;
  <strong>Colour</strong>: <select id='mapctrlcolour'>
	<option selected='selected' value='C:phase'>Settlement Phase</option>
	<optgroup label='Factions'>
	  @foreach ($factions as $faction)
	  <option value='F:{{$faction->name}}'>{{$faction->name}}</option>
	  @endforeach
	</optgroup>
  </select> ;
  <strong>Size</strong>: Population
</p>

<div id='cdbmapcontainer'>
  <canvas id='cdbmap' width='1200' height='1000'></canvas>
  <script type='text/javascript'>CDBMap.Init(
  [
  @foreach ($systems as $system)
  {
	'name' : "{{$system->displayName()}}",
	'x' : {{$system->coloniaCoordinates()->x}},
	'y' : {{$system->coloniaCoordinates()->y}},
	'z' : {{$system->coloniaCoordinates()->z}},
	'population' : {{$system->population}},
	@if ($system->inhabited())
	'controlling' : "{{$system->controllingFaction()->name}}",
	'factions' : [{!! $system->latestFactions()->map(function($x) { return '"'.$x->faction->name.'"'; })->implode(",") !!}],
	@else
	'controlling' : null,
	'factions' : [],
	@endif


	'phase' : {{$system->phase->sequence}}
  }
  @if (!$loop->last) , @endif
  @endforeach
  ]
  )
  </script>
</div>

@endsection
