@extends('layout/layout')

@section('title', 'System Map')

@section('content')

<p>
  <label for='mapctrlprojection'>Projection</label>: <select id='mapctrlprojection'>
	<option selected='selected'>XZ</option>
	<option>XY</option>
	<option>ZY</option>
  </select> ;
  <label for='mapctrllinks'>Links</label>: <select id='mapctrllinks'>
	<option selected='selected' value='C:mission'>Missions (15 LY)</option>
	<optgroup label='Expansions from'>
	  @foreach ($systems as $system)
	  <option value='S:{{$system->displayName()}}'>{{$system->displayName()}}</option>
	  @endforeach
	</optgroup>
  </select> ;
  <label for='mapctrlcolour'>Colour</label>: <select id='mapctrlcolour'>
	<option selected='selected' value='C:phase'>Settlement Phase</option>
	<option selected='selected' value='C:factions'>Factions Present</option>
	<optgroup label='Factions'>
	  @foreach ($factions as $faction)
	  <option value='F:{{$faction->name}}'>{{$faction->name}}</option>
	  @endforeach
	</optgroup>
	<optgroup label='Locations'>
	  @foreach ($facilities as $facility)
	  <option value='L:{{$facility->name}}'>{{$facility->name}}</option>
	  @endforeach
	</optgroup>
  </select> ;
  <label for='mapctrlsize'>Size</label>: <select id='mapctrlsize'>
	<option selected='selected' value='P'>Population</option>
	<option value='T'>Traffic</option>
	<option value='C'>Crime</option>
	<option value='B'>Bounties</option>
  </select> ;
  <label for='mapctrlfilter'>Filter?</label>: <select id='mapctrlfilter'>
	<option selected='selected' value='0'>All Systems</option>
	<option value='1'>Inhabited Only</option>
  </select>
</p>

<div id='cdbmapcontainer'>
  <canvas id='cdbmap' width='1200' height='1200'></canvas>
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
	'traffic' : {{$system->latestReport()->traffic}},
	'bounties' : {{$system->latestReport()->bounties}},
	'crime' : {{$system->latestReport()->crime}},
	@else
	'controlling' : null,
	'factions' : [],
	'traffic' : 0,
	'bounties' : 0,
	'crime' : 0,
	@endif
	'facilities' : [{!! $system->facilities->map(function($x) { return '"'.$x->name.'"'; })->implode(",") !!}],

	'phase' : {{$system->phase->sequence}}
  }
  @if (!$loop->last) , @endif
  @endforeach
  ]
  )
  </script>
</div>

@endsection
