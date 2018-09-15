@extends('layout/layout')

@section('title')
{{$project->summary}}
@endsection

@section('content')

@if ($userrank > 1)
<a class='edit' href='{{route('projects.edit', $project->id)}}'>Update</a>
@endif

@if($project->complete)
<p><strong>This project has been completed</strong></p>
@endif

<div><strong>Project Code:</strong> {{$project->code}}</div>

{!! $project->description !!}

@if ($project->url)
<p><a href='{{$project->url}}'>Further information</a></p>
@endif

@if ($project->objectives->count() > 0)
<h2>Objectives</h2>

<ul class='objectivelist'>
@foreach ($project->objectives as $objective) 
<li>
  {{$objective->label}} (code: <strong>{{$objective->code}}</strong>).
  Progress: {{ $objective->contributions->sum('amount')}} /
  @if ($objective->target)
  {{$objective->target}}
  @if ($objective->contributions->sum('amount') > $objective->target)
  - <strong>Complete</strong>
  @endif
  @else
  ???
  @endif
  @if (!$project->complete && ($objective->target > $objective->contributions->sum('amount') || !$objective->target))
  <div class='botbox' title='CensusBot Command'>
	CensusBot: <span class='command'>!contribute {{$project->code}} {{$objective->code}} [amount]</span>
  </div>
  @endif
</li>
@endforeach
</ul>



@endif


@endsection
