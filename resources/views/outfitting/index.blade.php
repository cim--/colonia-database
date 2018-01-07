@extends('layout/layout')

@section('title', 'Local Outfitting')

@section('content')

<p>The best outfitting is available from the stations which have a <a href="{{route('stations.index')}}#%22high-quality%22">"high quality outfitting"</a> facility. These tables summarise the module availability for the Colonia region as a whole.</p>

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
  <li>{{$mtype->description}}:
	@if ($mtype->modules->first()->stations_count > 0)
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
  <li>{{$mtype->description}}:
	@if ($mtype->modules->first()->stations_count > 0)
	@include('layout.yes')
	@else
	@include('layout.no')
	@endif
  </li>
  @endforeach
</ul>


@endsection
