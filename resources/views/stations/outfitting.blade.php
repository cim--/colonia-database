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

@if ($reqcurrent)
<p>Note: these modules are currently reported in stock at this station, but others may be possible to produce. See the individual module pages for details of stock levels, or <a href="{{route('stations.showoutfitting', $station->id)}}">view a summary of potential production</a>.</p>
@else
<p>Note: these modules are currently possible to produce at this station, but may not be in stock at this time. See the individual module pages for details of stock levels, or <a href="{{route('stations.showoutfitting.current', $station->id)}}">view a summary of current stock</a>.</p>
@endif

@include('outfitting.outfittinglist')
    
@endsection
