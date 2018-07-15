@extends('layout/layout')

@section('title')
Sites
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('sites.create')}}'>New site</a></p>
@endif
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>System</th>
	  <th>Planet</th>
	  <th>Coordinates</th>
	  <th>Summary</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($sites as $site)
	<tr>
	  <td data-sort='{{$site->system->displayName()}}'>
		@include($site->system->economy->icon)
		<a href='{{route('systems.show', $site->system_id)}}'>
		  {{$site->system->displayName()}}
		</a>
	  </td>
	  <td>
		{{$site->planet}}
	  </td>
	  <td>
		@if ($site->coordinates)
		{{$site->coordinates}}
		@else
		Orbital
		@endif
	  </td>
	  <td>
		<a href='{{route('sites.show', $site->id)}}'>{{$site->summary}}</a>
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
