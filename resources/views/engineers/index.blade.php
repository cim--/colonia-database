@extends('layout/layout')

@section('title')
Engineers
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('engineers.create')}}'>New engineer</a></p>
@endif

    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>System</th>
      <th>Planet</th>
      <th>Station</th>
	  <th>Blueprints</th>
      <th>Development Level</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($engineers as $engineer)
	<tr>
	  <td>
		<a href='{{route('engineers.show', $engineer->id)}}'>
		  {{$engineer->name}}
		</a>
	  </td>
	  <td data-sort='{{$engineer->station->system->displayName()}}'>
		@include($engineer->station->system->economy->icon)
		<a href='{{route('systems.show', $engineer->station->system_id)}}'>
		  {{$engineer->station->system->displayName()}}
		</a>
	  </td>
	  <td>
		{{$engineer->station->planet}}
	  </td>
	  <td data-sort='{{$engineer->station->displayName()}}'>
		<a href='{{route('stations.show', $engineer->station_id)}}'>
		  {{$engineer->station->displayName()}}
		</a>
	  </td>
	  <td>
		{{$engineer->blueprints->count()}}
	  </td>
	  <td>
		{{number_format($engineer->blueprints->average('level'),1)}}
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
