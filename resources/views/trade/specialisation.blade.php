@extends('layout/layout')

@section('title', 'Trade specialisation')

@section('content')

<ul class='compact'>
  @foreach ($economies as $economy)
  <li>
	<a href='{{route('specialisation.economy', $economy->id)}}'>
	  @include($economy->icon)
	  {{$economy->name}}
	</a>
  </li>
  @endforeach
</ul>

<p>This table shows the variation in baseline trade quantities, adjusted for economy size. Tonnage is normally proportional to the square root of the economy size.</p>

<p><a href="{{ route('specialisation.hybrid') }}">Estimate hybrid economy outputs</a></p>

<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th rowspan='2'>Category</th>
	  <th rowspan='2'>Name</th>
	  <th colspan='7'>Supply</th>
	  <th colspan='7'>Demand</th>
	  <th rowspan='2'>Median Ratio</th>
	</tr>
	<tr>
	    <th>Economies</th>
	    <th>Minimum</th>
	  <th>Low quartile</th>
	  <th>Median</th>
	  <th>High quartile</th>
	  <th>Maximum</th>
	  <th>Variation</th>
	  <th>Economies</th>
	  <th>Minimum</th>
	  <th>Low quartile</th>
	  <th>Median</th>
	  <th>High quartile</th>
	  <th>Maximum</th>
	  <th>Variation</th>

	</tr>
  </thead>
  <tbody>
	@foreach ($commodities as $commodity)
	@if ($commodity->commoditystat->supplymed || $commodity->commoditystat->demandmed)
	<tr>
	  <td>{{$commodity->category}}</td>
	  <td>
		<a href='{{route('reserves.commodity', $commodity->id)}}'>
		  {{$commodity->displayName()}}
		</a>
	  </td>
	  <td data-search="
			   @foreach ($commodity->exports ?? [] as $economy)
			   {{ $economy->name }}
			   @endforeach
			   ">
	      @foreach ($commodity->exports ?? [] as $economy)
		  @include($economy->icon)
	      @endforeach
	  </td>
	  <td>{{$commodity->commoditystat->supplymin}}</td>
	  <td>{{$commodity->commoditystat->supplylowq}}</td>
	  <td>{{$commodity->commoditystat->supplymed}}</td>
	  <td>{{$commodity->commoditystat->supplyhighq}}</td>
	  <td>{{$commodity->commoditystat->supplymax}}</td>
	  @if ($commodity->commoditystat->supplymin)
	  <td>{{number_format($commodity->commoditystat->supplymax/$commodity->commoditystat->supplymin,1)}}</td>
	  @else
	  <td></td>
	  @endif
	  <td data-search="
@foreach ($commodity->imports ?? [] as $economy)
	      {{ $economy->name }}
@endforeach
">
	      @foreach ($commodity->imports ?? [] as $economy)
		  @include($economy->icon)
	      @endforeach
	  </td>
	  <td>{{$commodity->commoditystat->demandmin}}</td>
	  <td>{{$commodity->commoditystat->demandlowq}}</td>
	  <td>{{$commodity->commoditystat->demandmed}}</td>
	  <td>{{$commodity->commoditystat->demandhighq}}</td>
	  <td>{{$commodity->commoditystat->demandmax}}</td>
	  @if ($commodity->commoditystat->demandmin)
	  <td>{{number_format($commodity->commoditystat->demandmax/$commodity->commoditystat->demandmin,1)}}</td>
	  @else
	  <td></td>
	  @endif
	  <td>
	      @if ($commodity->commoditystat->demandmed && $commodity->commoditystat->supplymed)
		  {{ number_format($commodity->commoditystat->supplymed/$commodity->commoditystat->demandmed, 2) }}
	      @endif
	  </td>
	</tr>
	@endif
	@endforeach
  </tbody>
</table>
@endsection
