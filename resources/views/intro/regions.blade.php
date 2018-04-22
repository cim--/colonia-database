@extends('layout/layout')

@section('title', 'Regional Comparison')

@section('content')

<p>This table compares Colonia to other major settled regions.</p>

<table id='regionalcomparison' class='table table-bordered'>
  <thead>
	<tr>
	  <th scope='col'>Property</th>
	  <th colspan='2' scope='col' class='native'>Colonia</th>
	  @foreach ($regions as $region)
	  <th colspan='2' scope='col' class='other'>{{$region->name}}</th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@include('intro/regionrow', ['label' => 'Systems', 'here' => $systemcount, 'there' => 'systems'])
	@include('intro/regionrow', ['label' => 'Stations', 'here' => $stationcount, 'there' => 'stations'])
	@include('intro/regionrow', ['label' => 'Factions', 'here' => $factioncount, 'there' => 'factions'])
	@include('intro/regionrow', ['label' => 'Population', 'here' => $totalPopulation, 'there' => 'population'])
	@include('intro/regionpcrow', ['label' => 'Reserves (T)', 'here' => $commodityReserves, 'there' => 'stock'])
	@include('intro/regionpcrow', ['label' => 'Demand (T)', 'here' => $commodityDemand, 'there' => 'demand'])
  </tbody>
  <tbody>
	@foreach ($economies as $economy)
	@include('intro/regioneconomyrow', ['economy' => $economy])
	@endforeach
  </tbody>
  <tbody>
	@foreach ($governments as $government)
	@include('intro/regiongovernmentrow', ['government' => $government])
	@endforeach
  </tbody>
  
</table>
	  

<p>Information on other regions estimated from <a href="https://eddb.io/api">EDDB API</a> on {{\App\Util::displayDate($regions[0]->updated_at)}}</p>
	
@endsection
