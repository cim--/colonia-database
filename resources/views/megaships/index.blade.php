@extends('layout/layout')

@section('title')
Megaships
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('megaships.create')}}'>New megaship</a></p>
@endif
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[1, "asc"]]'>
  <thead>
	<tr>
	  <th>Class</th>
	  <th>Serial</th>
	  <th>Current Location</th>
	  <th>Commissioned</th>
	  <th>Decommissioned</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($megaships as $megaship)
	<tr>
      <td>
		@include($megaship->megashipclass->icon)
		{{$megaship->megashipclass->name}}
	  </td>
      <td>
		<a href='{{route('megaships.show', $megaship->id)}}'>
		  {{$megaship->serial}}
		</a>
	  </td>
	  <td>
		@include('components.megashiplocation', ['location' => $megaship->currentLocation()])
	  </td>
	  @if ($megaship->commissioned)
	  <td data-sort='{{$megaship->commissioned->format('Y-m-d')}}'>
		{{\App\Util::displayDate($megaship->commissioned)}}
	  </td>
	  @else
	  <td data-sort='0'>
		Unknown
	  </td>
	  @endif
	  @if ($megaship->decommissioned)
	  <td data-sort='{{$megaship->decommissioned->format('Y-m-d')}}'>
		{{\App\Util::displayDate($megaship->decommissioned)}}
	  </td>
	  @else
	  <td data-sort='0'>
		In Service
	  </td>
	  @endif
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
