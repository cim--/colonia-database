@extends('layout/layout')

@section('title')
Shipyard at <a href="{{route('stations.show', $station->id)}}">{{$station->name}}</a>
@foreach ($station->currentStateList() as $state)
@include($state->icon)
@endforeach
@endsection
@section('headtitle')
Shipyard at {{$station->name}}
@endsection

@section('content')

@if ($ships->count())
<p>The following ships are available at this shipyard:</p>
<ul>
@foreach ($ships as $ship)
<li>{{$ship->name}}</li>
@endforeach
</ul>
@else
<p>Availability at this shipyard has not yet been recorded.</p>
@endif

@endsection
