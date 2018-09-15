@extends('layout/layout')

@section('title')
New Project
@endsection

@section('content')

{!! Form::open(['route' => 'projects.store']) !!}

@include('projects/form')

{!! Form::submit('Create project') !!}

{!! Form::close() !!}
    
@endsection
