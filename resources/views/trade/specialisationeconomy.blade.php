@extends('layout/layout')

@section('title', 'Trade specialisation for '.$economy->name)

@section('content')

@include('components.unstableanalysis')
    
<p>In the chart below, stations are horizontal lines, and commodities are vertical lines. The size of the bubble represents the relative production or consumption intensity of that commodity, and its colour whether it is an import or export. Hover over a bubble to see which station and commodity it represents</p>

<script type='text/javascript'>
  ChartCallbacks.data.commodities = {!! json_encode($clabels) !!};
  ChartCallbacks.data.stations = {!! json_encode($slabels) !!};
</script>
    
@include('layout/chart')
    
@endsection
