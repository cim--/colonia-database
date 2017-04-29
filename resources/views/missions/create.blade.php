@extends('layout/layout')

@section('title')
New Mission Type
@endsection

@section('content')

{!! Form::open(['route' => 'missions.store']) !!}

@include('missions/form')

{!! Form::submit('Create mission type') !!}

{!! Form::close() !!}
    
@endsection
