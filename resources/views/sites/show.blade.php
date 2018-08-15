@extends('layout/layout')

@section('title')
@if ($site->sitecategory)
{{$site->sitecategory->name}}: {{$site->summary}}
@else
{{$site->summary}}
@endif
@endsection

@section('content')
@include('components/trackbox', ['domain' => 'sites', 'id' => $site->id])
@if ($userrank > 1)
<a class='edit' href='{{route('sites.edit', $site->id)}}'>Update</a>
@endif

<div><strong>Location:</strong>
  <a href='{{route('systems.show', $site->system_id)}}'>
	{{$site->system->displayName()}}
  </a> {{$site->planet}}
  @if ($site->coordinates)
  {{$site->coordinates}}
  @else
  in orbit
  @endif
</div>

<div>
{!! $site->description !!}
</div>

@endsection
