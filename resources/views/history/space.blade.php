@extends('layout/layout')

@section('title')
Space Usage Trends
@endsection

@section('content')

    <p>The graph shows the average number of factions per system, and the number of retreats and expansions per week.</p>

@include('layout/chart')


    
@endsection
