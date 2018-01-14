@extends('layout/layout')

@section('title', 'Local Outfitting')

@section('content')

<p>The best outfitting is available from the stations which have a <a href="{{route('stations.index')}}#%22high-quality%22">"high quality outfitting"</a> facility. These tables summarise the module availability for the Colonia region as a whole.</p>

@include('outfitting.outfittinglist')

@endsection
