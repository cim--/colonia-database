@extends('layout/layout')

@section('title', 'Module availability: '.$module->displayName())

@section('content')

@include('outfitting/engineertext')
    
@if ($module->stations->count() > 0)
<p>This module is produced at the following stations. Note that stations in Lockdown will temporarily not have outfitting available.</p>

@if ($module->restricted)
<p>This module has pre-requisites (e.g. Horizons, Tech Broker, ranks) and will only be available to pilots who meet the pre-requisites.</p>
@endif

<ul class='compact'>
  @foreach ($module->stations->sortBy('name') as $station)
  <li>
	<a href='{{route('stations.showoutfitting', $station->id)}}'>{{$station->displayName()}}</a>
	@if ($station->currentState()->name == "Lockdown")
	@include($station->currentState()->icon)
	@endif
	@if ($module->largeship && !$station->stationclass->hasLarge)
	<span class='outfitting-danger-icon'>&#x2762;</span>
	@endif
    @if (!$station->pivot->current)
	<span class='outfitting-nostock-icon'>&#x2718;</span>
    @elseif ($station->pivot->unreliable)
    <span class='outfitting-lowstock-icon'>&#x2754;</span>
    @endif
  </li>
  @endforeach
</ul>

@if ($module->largeship && $module->stations->filter(function($s) {
return !$s->stationclass->hasLarge;
})->count() > 0)
<p><span class='outfitting-danger-icon'>&#x2762;</span> indicates that this station does not have a large landing pad, but no small or medium ship can fit this module.</p>
@endif
<p><span class='outfitting-nostock-icon'>&#x2718;</span> indicates that this station is currently out of stock, while <span class='outfitting-lowstock-icon'>&#x2754;</span> indicates that the station currently has stock but sometimes does not.</p>


@else
<p>This module is not available in the Colonia region</p>

@if ($module->restricted)
<p>This module has pre-requisites (e.g. Horizons, Tech Broker, ranks) and will only be available to pilots who meet the pre-requisites.</p>
@endif

@endif

@if (!$singular)
<p><a href='{{route('outfitting.moduletype', $moduletype->id)}}'>Check availability of other {{$moduletype->description}} classes</a></p>
@endif

@endsection
