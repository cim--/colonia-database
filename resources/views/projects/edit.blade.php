@extends('layout/layout')

@section('title')
{{$project->summary}} - edit
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($project, ['route' => ['projects.update', $project->id], 'method' => 'PUT']) !!}

@include('projects/form')

{!! Form::submit('Update project') !!}

{!! Form::close() !!}
</div>

@endsection
