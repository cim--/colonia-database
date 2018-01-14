<ul class='compact'>
  <li><a href='#coremod'>Core modules</a></li>
  <li><a href='#optmod'>Optional modules</a></li>
  <li><a href='#armours'>Armour</a></li>
  <li><a href='#hardpoints'>Hardpoints</a></li>
  <li><a href='#utilities'>Utilities</a></li>
</ul>

<h2 id='coremod'>Core Modules</h2>
@include('outfitting.sizedtable', ['types' => $coremodules])	  

<h2 id='optmod'>Optional Modules</h2>
@include('outfitting.sizedtable', ['types' => $optmodules])	  

<ul class='compact'>
  @foreach ($optnsmodules as $mtype)
  <li>
	<a href='{{route('outfitting.moduletype', $mtype->id)}}'>{{$mtype->description}}</a>:
	@if ($mtype->modules->count() > 0 && $mtype->modules->first()->stations_count > 0)
	@include('layout.yes')
	@else
	@include('layout.no')
	@endif
  </li>
  @endforeach
</ul>

<h2 id='armours'>Armours</h2>
@include('outfitting.armourtable')

<h2 id='hardpoints'>Hardpoints</h2>
@include('outfitting.weapontable')

<h2 id='utililities'>Utilities</h2>

@include('outfitting.utilitytable')

<ul class='compact'>
  @foreach ($utilitiesns as $mtype)
  <li>
	<a href='{{route('outfitting.moduletype', $mtype->id)}}'>{{$mtype->description}}</a>:
	@if ($mtype->modules->count() > 0 && $mtype->modules->first()->stations_count > 0)
	@include('layout.yes')
	@else
	@include('layout.no')
	@endif
  </li>
  @endforeach
</ul>
