@extends('layout/layout')

@if ($station !== null)
@section('title', 'Regional Reserves - '.$commodity->displayName().' - '.$station->name)
@else 
@section('title', 'Regional Reserves - '.$commodity->displayName())
@endif

@section('content')

<p>Total estimated reserves: {{number_format($reserves->filter(function($v) { return $v->reserves > 0; })->sum('reserves')) }}</p>
<p>Total estimated demand: {{number_format(-$reserves->filter(function($v) { return $v->reserves < 0; })->sum('reserves')) }}</p>

<table class='table table-bordered datatable' data-page-length='25'
	   @if($station !== null)
	   data-order='[[5, "asc"]]'
	   @endif
	   >
  <thead>
	<tr>
	  <th>System</th>
	  <th>Station</th>
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
		<a href="{{route('reserves.commodity.reference', [$reserve->commodity_id, $reserve->station->id])}}">
		  {{$reserve->station->name}}
		</a>
		@include($reserve->station->economy->icon)
	  </td>
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
	  @if ($station !== null)
	  <td>
		{{number_format($station->system->distanceTo($reserve->station->system), 2)}}
	  </td>
	  @endif
	  <td>
		{{$reserve->created_at->diffForHumans()}}
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
