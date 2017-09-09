@extends('layout/layout')

@section('title')
Activity Trends
@endsection

@section('content')

<p>The graph shows the total traffic, crime and bounties activities across the region. Activity is estimated for most days due to traffic reports being partial only.</p>

@include('layout/chart')

@endsection
