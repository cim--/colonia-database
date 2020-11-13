@extends('layout/layout')

@section('title', 'System Map')

@section('content')

<p><strong>Nebulae shown to scale but the distance between them has been compressed to fit them on the same map.</strong></p>
    
<div id='mapkeys'>
  <div id='mapkeysphase'>
	<strong>Settlement phase</strong>: red to yellow (older to newer);
  </div>
  <div id='mapkeyspresent'>
	<strong>Factions present</strong>: grey empty; green to orange
	increasingly populated; red overpopulated
  </div>
  <div id='mapkeyscontrol'>
	<strong>Faction control</strong>: dark grey empty; colours represent factions controlling 2 or more systems; white other factions controlling a single system. Filled circles indicate systems controlled by a native faction
  </div>
  <div id='mapkeysdepth'>
	<strong>Depth</strong>: blue closer to viewer, green level with
	HIP 23759, red further away
  </div>
  <div id='mapkeysfaction'>
	<strong>Specific faction</strong>: red controls; yellow present;
	green other populated; blue other populated (7 others); grey
	uninhabited
  </div>
  <div id='mapkeyslocation'>
	<strong>Location type</strong>: yellow yes, grey no
  </div>
  <div id='mapkeyssite'>
	<strong>Location type</strong>: yellow yes, grey no
  </div>
</div>
<div id='mapctrls'>
<p>
  <label for='mapctrlprojection'>Projection</label>: <select id='mapctrlprojection'>
	<option selected='selected'>XZ</option>
	<option>XY</option>
	<option>ZY</option>
  </select> ;
  <label for='mapctrllinks'>Links</label>: <select id='mapctrllinks'>
	<option value='C:off'>Off</option>
	<option selected='selected' value='C:mission'>Missions (20 LY)</option>
	<!--    <option selected='selected' value='C:courier'>Courier Missions (10 LY)</option> -->
	<option value='C:control'>Controlling faction</option>
	<optgroup label='Expansions from'>
	  @foreach ($systems as $system)
	  <option value='S:{{$system->displayName()}}'>{{$system->displayName()}}</option>
	  @endforeach
	</optgroup>
  </select> ;
  <label for='mapctrlcolour'>Colour</label>: <select id='mapctrlcolour'>
	<option selected='selected' value='C:phase'>Settlement Phase</option>
	<option value='C:factions'>Factions Present</option>
    <option value='C:control'>Faction Control</option>
	<option value='C:depth'>Depth</option>
	<optgroup label='Points of Interest'>
	  <option value='P:megaship'>Megaship</option>
	  <option value='P:megashiproute'>Megaship Route</option>
	  <option value='P:installation'>Installation</option>
	  <option value='P:site'>Site</option>
	</optgroup>
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
	<option value='X'>Off</option>
	<option selected='selected' value='P'>Population</option>
	<option value='T'>Traffic</option>
	<option value='C'>Crime</option>
	<option value='B'>Bounties</option>
  </select> ;
  <label for='mapctrlfilter'>Filter?</label>: <select id='mapctrlfilter'>
	<option selected='selected' value='0'>All Systems</option>
	<option value='1'>Inhabited Only</option>
	<option value='2'>Shipyards Only</option>
    <option value='3'>Large Pads Only</option>
    <option value='4'>Orbitals Only</option>
  </select>
</p>
<p>
  <label for='mapctrlfade'>Fade?</label>:
  <input id='mapctrlfade' type='checkbox' value='1'> ;
  <label for='mapctrlfadeslider'>Focus Depth:</label>
  <input type='range' id='mapctrlfadeslider' min='-50' max='50' value='0'>
</p>
</div>                                      
<div id='cdbmapcontainer'>
  <canvas id='cdbmap' width='1200' height='1200'></canvas>
  <script type='text/javascript'>CDBMap.Init(
  [
  @foreach ($systems as $system)
  {
	'name' : "{!! $system->displayName() !!}",
	'x' : {{$system->coloniaCoordinates()->x}},
	'y' : {{$system->coloniaCoordinates()->y}},
	'z' : {{$system->coloniaCoordinates()->z}},
	'population' : {{$system->population}},
	@if ($system->inhabited())
	'controlling' : "{{$system->controllingFaction()->name}}",
    @if ($system->controllingFaction()->stations->where('primary', 1)->count() > 1)
    'controlcolour' : '#{{$system->controllingFaction()->colour()}}',
    @else
    'controlcolour' : '#ffffff',
    @endif
	'nativecontrol' : {{ $system->controllingFaction()->system_id == $system->id ? 1 : 0 }},
	'factions' : [{!! $system->latestFactions()->map(function($x) { return '"'.$x->faction->name.'"'; })->implode(",") !!}],
	'traffic' : {{$system->latestReport()->traffic}},
	'bounties' : {{$system->latestReport()->bounties}},
	'crime' : {{$system->latestReport()->crime}},
	@if ($system->mainStation())
	'shipyard' : {{ $system->mainStation()->facilities()->where('name', 'Shipyard')->count() > 0 ? 1 : 0 }},
    'largepad' : {{ $system->mainStation()->stationclass->hasLarge ? 1 : 0 }},
    'orbitals' : {{ $system->stations->where('gravity', null)->count() ? 1 : 0 }},
	@else
	'shipyard' : 0,
    'largepad' : 0,
    'orbitals' : 0,
	@endif
	'sites' : {
	'megaship' : {{ $system->megashiproutes->filter(function($x) { return $x->nextArrival() == null; })->count() }},
	'megashiproute' : {{ $system->megashiproutes->count() }},
	'installation' : {{ $system->installations_count }},
	'site' : {{ $system->sites_count }},
	},
	@else
	'controlling' : null,
    'controlcolour' : '#444444',
	'nativecontrol' : 0,
	'factions' : [],
	'traffic' : 0,
	'bounties' : 0,
	'crime' : 0,
	'shipyard' : 0,
	'largepad' : 0,
    'orbitals' : 0,
	'sites' : {
	'megaship' : false,
	'megashiproute' : false,
	'installation' : false,
	'site' : false,
	},
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
