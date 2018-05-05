@extends('layout/layout')

@section('title', 'Regional Comparison')

@section('content')

<p>This table compares Colonia to other major settled regions and political entities.</p>

<h2>Size and population</h2>
@include('intro/regiontablehead')
<tbody>
  @include('intro/regionrow', ['label' => 'Systems', 'here' => $systemcount, 'there' => 'systems'])
  @include('intro/regionrow', ['label' => 'Stations', 'here' => $stationcount, 'there' => 'stations'])
  @include('intro/regionrow', ['label' => 'Factions', 'here' => $factioncount, 'there' => 'factions'])
  @include('intro/regionrow', ['label' => 'Population', 'here' => $totalPopulation, 'there' => 'population'])
  @include('intro/regionpcrow', ['label' => 'Reserves (T)', 'here' => $commodityReserves, 'there' => 'stock'])
  @include('intro/regionpcrow', ['label' => 'Demand (T)', 'here' => $commodityDemand, 'there' => 'demand'])
</tbody>
@include('intro/regiontablefoot')

<h2>System Economies</h2>
@include('intro/regiontablehead')
<tbody>
  @foreach ($economies as $economy)
  @include('intro/regioneconomyrow', ['economy' => $economy])
  @endforeach
</tbody>
@include('intro/regiontablefoot')

<h2>Station Economies</h2>
@include('intro/regiontablehead')
<tbody>
  @foreach ($economies as $economy)
  @include('intro/regionstationeconomyrow', ['economy' => $economy])
  @endforeach
</tbody>
@include('intro/regiontablefoot')

<h2>Faction Governments</h2>
@include('intro/regiontablehead')
<tbody>
  @foreach ($governments as $government)
  @include('intro/regiongovernmentrow', ['government' => $government])
  @endforeach
</tbody>
@include('intro/regiontablefoot')
  
</table>
	  

<p>Information on other regions estimated from <a href="https://eddb.io/api">EDDB API</a> on {{\App\Util::displayDate($regions[0]->updated_at)}}</p>
	
@endsection
