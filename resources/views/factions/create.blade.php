@extends('layout/layout')

@section('title')
New Faction
@endsection

@section('content')

{!! Form::open(['route' => 'factions.store']) !!}

@include('factions/form')

{!! Form::submit('Create faction') !!}

{!! Form::close() !!}
    
@endsection
