@extends('layout/layout')

@section('title')
Projects
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('projects.create')}}'>New project</a></p>
@endif

<p>The following projects are available to collect census data or to improve facilities in the region.</p>

<ul>
@foreach ($projects as $project)
<li>
  <a href='{{route('projects.show', $project->id)}}'>{{$project->summary}}</a>
  @if ($project->complete)
  - <strong>Completed</strong>
  @endif
</li>
@endforeach
</ul>

    
@endsection
