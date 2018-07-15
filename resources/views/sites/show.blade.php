@extends('layout/layout')

@section('title')
{{$site->summary}}
@endsection

@section('content')
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
