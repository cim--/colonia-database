@extends('layout/layout')

@section('title')
New Installation
@endsection

@section('content')

{!! Form::open(['route' => 'installations.store']) !!}

@include('installations/form')

{!! Form::submit('Create installation') !!}

{!! Form::close() !!}
    
@endsection
