@extends('layout/layout')

@section('title')
Projects
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('projects.create')}}'>New project</a></p>
@endif

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
