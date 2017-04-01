@extends('layout/layout')

@section('title')
New Station
@endsection

@section('content')

{!! Form::open(['route' => 'stations.store']) !!}

@include('stations/form')

{!! Form::submit('Create station') !!}

{!! Form::close() !!}
    
@endsection
