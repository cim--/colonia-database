@extends('layout/layout')

@if ($module->moduletype->type == "suit" || $module->moduletype->type == "personalweapon")
@section('title', 'Blueprint availability: '.$module->displayName())
@else
@section('title', 'Module availability: '.$module->displayName())
@endif

@section('content')

@include('outfitting/engineertext')

@if ($module->moduletype->type == "suit" || $module->moduletype->type == "personalweapon")

<p>All base suits and weapons are available in the region. Pre-engineered items are possible to find but rare.</p>

@elseif ($module->stations->count() > 0)
<p>This module is produced at the following stations. Note that stations in Lockdown will temporarily not have outfitting available.</p>

@if ($module->restricted)
<p>This module has pre-requisites (e.g. Horizons, Tech Broker, ranks) and will only be available to pilots who meet the pre-requisites.</p>
@endif

<form action='{{route('outfitting.module', [$moduletype->id, $module->id])}}'>
  <div>
    Change reference system: <select name='reference'>
      @foreach ($systems as $system)
      <option value='{{$system->id}}' @if ($system->id == $reference->id)
	selected='selected'
	@endif>
	{{$system->displayName()}}
      </option>
      @endforeach
    </select>
    <input type='submit' value='Update'>
  </div> 
</form>

<table class='table table-bordered datatable'>
  <thead>
    <tr>
      <th>System</th>
      <th>Station</th>
      <th>Station type</th>
      <th>Availability</th>
      <th>Distance to {{$reference->displayName()}}</th>
      <th>Last Update</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($module->stations->sortBy('name') as $station)
    <tr>
      <td>
	<a href='{{route('systems.show', $station->system_id)}}'>
	  {{$station->system->displayName()}}
	</a>
      </td>
      <td>
	<a href='{{route('stations.showoutfitting', $station->id)}}'>{{$station->displayName()}}</a>
      </td>
      <td>
	{{$station->stationclass->name}}
      </td>
      <td>
	@if ($station->currentStateList()->where('name', "Lockdown")->count() > 0)
	@include('icons/states/lockdown') Lockdown
	@elseif ($module->largeship && !$station->stationclass->hasLarge)
	<span class='outfitting-danger-icon'>&#x2762;</span> No Pad
	@elseif (!$station->pivot->current)
	<span class='outfitting-nostock-icon'>&#x2718;</span> No Stock
	@elseif ($station->pivot->unreliable)
	<span class='outfitting-lowstock-icon'>&#x2754;</span> Available (unreliable)
	@else
	Available
	@endif
      </td>
      <td>
	{{number_format($station->system->distanceTo($reference),2)}}
      </td>
      <td data-sort="{{$station->pivot->updated_at ? $station->pivot->updated_at->timestamp : 0}}">
	{{\App\Util::displayDate($station->pivot->updated_at)}}
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

@if ($module->largeship && $module->stations->filter(function($s) {
return !$s->stationclass->hasLarge;
})->count() > 0)
<p><span class='outfitting-danger-icon'>&#x2762;</span> indicates that this station does not have a large landing pad, but no small or medium ship can fit this module.</p>
@endif
<p><span class='outfitting-nostock-icon'>&#x2718;</span> indicates that this station is currently out of stock, while <span class='outfitting-lowstock-icon'>&#x2754;</span> indicates that the station currently has stock but sometimes does not.</p>


@else
<p>This module is not available in the Witch Head region</p>

@if ($module->restricted)
<p>This module has pre-requisites (e.g. Horizons, Tech Broker, ranks) and will only be available to pilots who meet the pre-requisites.</p>
@endif

@endif

@if (!$singular)
<p><a href='{{route('outfitting.moduletype', $moduletype->id)}}'>Check availability of other {{$moduletype->description}} classes</a></p>
@endif

@endsection
