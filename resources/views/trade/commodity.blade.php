@extends('layout/layout')

@if ($station !== null)
@section('title', 'Regional Reserves - '.$commodity->displayName().' - '.$station->name)
@else 
@section('title', 'Regional Reserves - '.$commodity->displayName())
@endif

@section('content')

<div class='commodityhead'>

<div>
<p>Total estimated reserves: {{number_format($reserves->filter(function($v) { return $v->reserves > 0; })->sum('reserves')) }}
  @if ($commodity->supplycycle)
  (restock cycle: {{number_format($commodity->supplycycle/86400, 1)}} days)
  @endif
</p>
<p>Total estimated demand: {{number_format(-$reserves->filter(function($v) { return $v->reserves < 0; })->sum('reserves')) }}
  @if ($commodity->demandcycle)
  (usage cycle: {{number_format(-$commodity->demandcycle/86400, 1)}} days)
  @endif
</p>
<p>Use the &#x21c4; icons to sort the table by distance to this station.</p>
</div>

<ul class='commoditynav'>
  <li><a href='{{route('reserves.commodity.history', $commodity->id)}}'>Reserves History</li>
  <li><a href='{{route('effects.commodity', $commodity->id)}}'>State effects</li>
</ul>
</div>

<table class='table table-bordered datatable' data-page-length='25'
	   @if($station !== null)
	   data-order='[[5, "asc"]]'
	   @endif
	   >
  <thead>
	<tr>
	  <th>System</th>
	  <th>Station</th>
      <th>Docking</th>
	  <th>Status</th>
	  <th>Stock/Demand</th>
	  <th>Price</th>
	  @if ($station !== null)
	  <th>Distance to {{$station->name}} (LY)</th>
	  @endif
	  <th>Updated at</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($reserves as $reserve)
	<tr>
	  <td>
		<a href="{{route('systems.show', $reserve->station->system->id)}}">
		  {{$reserve->station->system->displayName()}}
		</a>
	  </td>
	  <td>
		<a href="{{route('stations.showtrade', $reserve->station->id)}}">
		  {{$reserve->station->name}}
		</a>
		@include($reserve->station->economy->icon)
		<a href="{{route('reserves.commodity.reference', [$reserve->commodity_id, $reserve->station->id])}}" title='Relative to {{$reserve->station->name}}'>&#x21c4;</a>
	  </td>
	  <td data-search='
		@if ($reserve->station->stationclass->hasSmall) Small Pad @endif
		@if ($reserve->station->stationclass->hasMedium) Medium Pad @endif
		@if ($reserve->station->stationclass->hasLarge) Large Pad @endif
		  '>
		@if ($reserve->station->stationclass->hasSmall) S @endif
		@if ($reserve->station->stationclass->hasMedium) M @endif
		@if ($reserve->station->stationclass->hasLarge) L @endif
	  </td>
	  @if ($reserve->station->currentState()->name == "Lockdown")
	  <td>Lockdown</td>
	  <td>
		<span class='lockdown'>{{$reserve->reserves}}</span>
	  </td>
	  <td>
		<span class='lockdown'>{{$reserve->price}}</span>
	  </td>
	  @else
	  <td>
		@if ($reserve->reserves > 0)
		Exports
		@else
		Imports
		@endif
	  </td>
	  <td>
		@if ($reserve->reserves > 0)
		<span class='surplus'>{{$reserve->reserves}}</span>
		@else
		<span class='deficit'>{{$reserve->reserves}}</span>
		@endif
	  </td>
	  <td>
		{{$reserve->price}}
	  </td>
	  @endif
	  @if ($station !== null)
	  <td>
		{{number_format($station->system->distanceTo($reserve->station->system), 2)}}
	  </td>
	  @endif
	  <td data-sort="{{$reserve->created_at->timestamp}}">
		{{$reserve->created_at->diffForHumans()}}
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
