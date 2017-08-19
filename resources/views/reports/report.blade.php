@extends('layout/layout')

@section('title', $report.' Reports')

@section('content')

{!! $chart->render() !!}    

@endsection
