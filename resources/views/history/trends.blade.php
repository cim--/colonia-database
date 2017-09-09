@extends('layout/layout')

@section('title')
Activity Trends
@endsection

@section('content')

<p>The graph shows the total traffic, crime and bounties activities across the region. Activity is estimated for most days due to only partial collection of report data being practical.</p>

@include('layout/chart')

@endsection
