@extends('layout/layout')

@section('title', $report.' Reports')

@section('content')

<ul class='compact'>
  @foreach (['Traffic', 'Crime', 'Bounties'] as $type)
  @if ($report != $type)
  <li><a href='{{route('reports')}}?type={{strtolower($type)}}'>{{$type}}</a></li>
  @endif
  @endforeach
</ul>

{!! $chart->render() !!}    

@endsection
