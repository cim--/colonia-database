@extends('layout/layout')

@section('title', 'Local Outfitting')

@section('content')

<p>The best outfitting is available from the stations which have a <a href="{{route('stations.index')}}#%22high-quality%22">"high quality outfitting"</a> facility. These tables summarise the module production for the Witch Head region as a whole as well as the <a href='{{route('engineers.index')}}'>engineering blueprints</a> available locally.</p>

@if ($reqcurrent)
<p>Note: other modules may be possible to produce but not currently in stock. See the individual module pages for details of stock levels, or <a href="{{route('outfitting')}}">view a summary of production capability</a>.</p>
@else
<p>Note: these modules are currently possible to produce, but may not be in stock at this time. See the individual module pages for details of stock levels, or <a href="{{route('outfitting.current')}}">view a summary of current stock</a>.</p>
@endif

@include('outfitting.outfittinglist')

@endsection
