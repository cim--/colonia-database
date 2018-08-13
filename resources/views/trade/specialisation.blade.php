@extends('layout/layout')

@section('title', 'Trade specialisation')

@section('content')

<p>This table shows the variation in baseline trade quantities, adjusted for economy size. Tonnage is normally proportional to the square root of the economy size.</p>

<table class='table table-bordered datatable'>
  <thead>
	<tr>
	  <th rowspan='2'>Category</th>
	  <th rowspan='2'>Name</th>
	  <th colspan='6'>Supply</th>
	  <th colspan='6'>Demand</th>
	</tr>
	<tr>
	  <th>Minimum</th>
	  <th>Low quartile</th>
	  <th>Median</th>
	  <th>High quartile</th>
	  <th>Maximum</th>
	  <th>Variation</th>
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
	</tr>
	@endif
	@endforeach
  </tbody>
</table>
@endsection
