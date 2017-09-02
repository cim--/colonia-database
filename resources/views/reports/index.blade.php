@extends('layout/layout')

@section('title', 'Reports')

@section('content')

<ul>
   <li><a href="{{route('reports.traffic')}}">Traffic</a></li>
   <li><a href="{{route('reports.crimes')}}">Crime</a></li>
   <li><a href="{{route('reports.bounties')}}">Bounties</a></li>
   <li><a href="{{route('reports.control')}}">Control</a></li>
   <li><a href="{{route('reports.reach')}}">Reach</a></li> 
</ul>


@endsection
