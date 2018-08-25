@extends('layout/layout')

@section('title')
New Engineer
@endsection

@section('content')

{!! Form::open(['route' => 'engineers.store']) !!}

@include('engineers/form')

{!! Form::submit('Create engineer') !!}

{!! Form::close() !!}
    
@endsection
