@extends('layout/layout')

@section('title', 'Module availability: '.$module->displayName())

@section('content')

@if ($module->stations->count() > 0)
<p>This module is available at the following stations. Note that stations in Lockdown will temporarily not have outfitting available.</p>

<ul class='compact'>
  @foreach ($module->stations as $station)
  <li>
	<a href='{{route('stations.show', $station->id)}}'>{{$station->displayName()}}</a>
	@if ($station->currentState()->name == "Lockdown")
	@include($station->currentState()->icon)
	@endif
	@if ($module->largeship && !$station->stationclass->hasLarge)
	<span class='outfitting-danger-icon'>&#x2762;</span>
	@endif
  </li>
  @endforeach
</ul>

@if ($module->largeship && $module->stations->filter(function($s) {
return !$s->stationclass->hasLarge;
})->count() > 0)
<p><span class='outfitting-danger-icon'>&#x2762;</span> indicates that this station does not have a large landing pad, but no small or medium ship can fit this module.</p>
@endif

@else
<p>This module is not available in the Colonia region</p>
@endif

@if (!$singular)
<p><a href='{{route('outfitting.moduletype', $moduletype->id)}}'>Check availability of other {{$moduletype->description}} classes</a></p>
@endif

@endsection
