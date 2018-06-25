@extends('layout/layout')

@section('title')
Shipyard Availability
@endsection

@section('content')

<table class='table table-bordered'>
<thead>
  <tr>
	<th>Ship</th><th>Available?</th>
  </tr>
</thead>
<tbody>
@foreach ($ships as $ship)
<tr>
  <td>
	<a href="{{route('outfitting.shipyard.ship', $ship->id)}}">
	  {{$ship->name}}
	</a>
  </td>
  <td>
	@if ($ship->stations_count > 0)
	@include('layout/yes')
	({{$ship->stations_count}}
	@if ($ship->stations_count > 1)
	stations)
	@else
	station)
	@endif
	@else
	@include('layout/no')
	@endif
  </td>
</tr>
@endforeach
</tbody>
</table>
@endsection
