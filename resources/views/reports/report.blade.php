@extends('layout/layout')

@section('title', $report.' Reports')

@section('content')

<p>{!! $desc !!}</p>
    
{!! $chart->render() !!}    

@endsection
