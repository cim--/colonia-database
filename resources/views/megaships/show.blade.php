@extends('layout/layout')

@section('title')
{{$megaship->displayName()}}
@endsection

@section('content')
<div><strong>Commissioned:</strong>
  @if ($megaship->commissioned)
  {{\App\Util::displayDate($megaship->commissioned)}}
  @else
  Unknown
  @endif
</div>
<div><strong>Decommissioned:</strong>
  @if ($megaship->decommissioned)
  {{\App\Util::displayDate($megaship->decommissioned)}}
  @else
  In Service
  @endif
</div>

@if ($megaship->cargodesc)
<p><strong>Typical cargo:</strong> {{$megaship->cargodesc}}</p>
@endif

<h2>Itinerary</h2>
@if ($megaship->megashiproutes->count() > 0)
<table class='table table-bordered datatable' data-paging='0' data-searching='0' data-info='0' data-order='[[2, "asc"]]'>
  <thead>
	<tr><th>System</th><th>Next arrival</th><th>Next departure</th></tr>
  </thead>
  <tbody>
	@foreach ($megaship->megashiproutes as $route)
	<tr
	   @if (!$route->nextArrival())
	  class='megashipcurrentlocation'
	  @endif
	  >
	  <td>
		@if ($route->system)
		<a href="{{route('systems.show', $route->system->id)}}">
		  @include($route->system->economy->icon)
		  {{$route->system->displayName()}}
		</a>
		@else
		{{$route->systemdesc}}
		@endif
	  </td>
	  <td data-sort='{{$route->nextArrival()?$route->nextArrival()->format("Y-m-d"):0}}'>
		@if ($route->nextArrival())
		{{App\Util::displayDate($route->nextArrival())}}
		@else
		<strong>Present</strong>
		@endif
	  </td>
	  <td data-sort='{{$route->nextDeparture()->format("Y-m-d")}}'>
		{{App\Util::displayDate($route->nextDeparture())}}
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
@else
<p>Itinerary not known.</p>
@endif

@endsection
