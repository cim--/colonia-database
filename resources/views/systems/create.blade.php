@extends('layout/layout')

@section('title')
New System
@endsection

@section('content')

{!! Form::open(['route' => 'systems.store']) !!}

@include('systems/form')

{!! Form::submit('Create system') !!}

{!! Form::close() !!}
    
@endsection
