@extends('layout/layout')

@section('title')
Sites
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('sites.create')}}'>New site</a></p>
@endif

<p>Not all tip-off sites will be visitable simultaneously. Obtain the appropriate information before visiting to avoid disappointment.</p>
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"],[1, "asc"]]'>
  <thead>
	<tr>
	  <th>System</th>
	  <th>Planet</th>
	  <th>Location</th>
      <th>Category</th>
	  <th>Summary</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($sites as $site)
	<tr>
	  <td data-sort='{{$site->system->displayName()}}'>
            @if ($site->system->economy)
	    @include($site->system->economy->icon)
	    @endif
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
		@if ($site->sitecategory)
		{{$site->sitecategory->name}}
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
