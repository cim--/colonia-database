@extends('layout/layout')

@section('title')
Outfitting at <a href="{{route('stations.show', $station->id)}}">{{$station->name}}</a>
@include($station->currentState()->icon)
@endsection
@section('headtitle')
Outfitting at {{$station->name}}
@endsection

@section('content')

@if ($station->currentState()->name == "Lockdown")
<p><strong>Station is currently in Lockdown - outfitting unavailable.</strong> Showing last known state.</p>
@endif

@include('outfitting.outfittinglist')
    
@endsection
