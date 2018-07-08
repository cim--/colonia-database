@extends('layout/layout')

@section('title')
Installations
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('installations.create')}}'>New installation</a></p>
@endif
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>System</th>
	  <th>Planet</th>
	  <th>Type</th>
	  <th>Satellites</th>
	  <th>Trespass</th>
	  <th>Cargo</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($installations as $installation)
	<tr>
	  <td data-sort='{{$installation->system->displayName()}}'>
		@include($installation->system->economy->icon)
		<a href='{{route('systems.show', $installation->system_id)}}'>
		  {{$installation->system->displayName()}}
		</a>
	  </td>
	  <td>
		{{$installation->planet}}
	  </td>
      <td data-sort='{{$installation->installationclass->name}}'>
		@include($installation->installationclass->icon)
		<a href='{{route('installations.show', $installation->id)}}'>
		  {{$installation->installationclass->name}}
		  @if ($installation->name)
		  ({{$installation->name}})
		  @endif
		</a>
	  </td>
      <td>
		@if ($installation->satellites)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	  <td>
		@if ($installation->trespasszone)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	  <td>
		@if ($installation->cargo)
		{{$installation->cargo}}
		@else
		Survey pending
		@endif
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
