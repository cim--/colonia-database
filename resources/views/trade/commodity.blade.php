@extends('layout/layout')

@section('title', 'Regional Reserves - '.$commodity->displayName())

@section('content')

<p>Total estimated reserves: {{$reserves->filter(function($v) { return $v->stock > 0; })->sum('reserves') }}</p>
<p>Total estimated demand: {{-$reserves->filter(function($v) { return $v->stock < 0; })->sum('reserves') }}</p>

<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th>System</th>
	  <th>Station</th>
	  <th>Status</th>
	  <th>Stock/Demand</th>
	  <th>Price</th>
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
		<a href="{{route('stations.show', $reserve->station->id)}}">
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
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
