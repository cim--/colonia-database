@extends('layout/layout')

@section('title', 'Regional Reserves')

@section('content')

<p>
  Total surplus/deficit:
  <strong>
  @if ($total >= 0)
  <span class='surplus'>{{number_format($total)}}</span>
  @else
  <span class='deficit'>{{number_format($total)}}</span>
  @endif
  </strong>
  tonnes. (
  <strong>
  @if ($tradetotal >= 0)
  <span class='surplus'>{{number_format($tradetotal)}}</span>
  @else
  <span class='deficit'>{{number_format($tradetotal)}}</span>
  @endif
  </strong>
  tonnes excluding mined/salvaged/etc. goods).<br>
  Total demand: {{number_format($demandtotal)}} tonnes (baseline {{number_format($nominaldemandtotal)}} tonnes)<br>
  Total reserves: {{number_format($stocktotal)}} tonnes (baseline {{number_format($nominalstocktotal)}} tonnes)
</p>

@if ($totalstations > $stations)
<p class='alert alert-danger'>Warning: based on data from {{$stations}} / {{$totalstations}} stations only</p>
@endif

<p>Oldest data: {{App\Util::displayDate($oldest)}}</p>


<table id='reservestable' class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"],[1, "asc"]]'>
  <thead>
	<tr>
      <th>Category</th>
	  <th>Commodity</th>
	  <th>Current Reserves</th>
	  <th>Current Demand</th>
	  <th title='Current stocks/demand difference in tonnes'>Current Surplus</th>
	  <th>Baseline Reserves</th>
	  <th>Baseline Demand</th>
      <th title='Baseline stocks/demand difference in tonnes'>Baseline Surplus</th>
	  <th title='How long it takes an empty station to produce to full capacity'>Production Cycle</th>
      <th title='How long it takes a filled station to return to full demand'>Consumption Cycle</th>
	  <th title='Current stocks/demand difference adjusting for differences in production and consumption cycles'>Daily Baseline Surplus</th>
	  <th>Exported</th>
	  <th>Imported</th>
	  <th>Buy</th>
	  <th>Sell</th>
	  <th>History</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($commodities as $commodity)
	<tr>
	  <td>
		{{$commodity['category']}}
	  </td>
	  <td>
		<a href="{{route('reserves.commodity', $commodity['id'])}}">
		  {{$commodity['name']}}
		</a>
	  </td>
	  <td>{{number_format($commodity['stock'])}}</td>
	  <td>{{number_format($commodity['demand'])}}</td>
	  @if ($commodity['demand'] > $commodity['stock'])
	  <td><span class='deficit'>{{$commodity['stock'] - $commodity['demand']}}</span></td>
	  @else
	  <td><span class='surplus'>{{$commodity['stock'] - $commodity['demand']}}</span></td>
	  @endif
	  <td>{{number_format($commodity['baselinestock'])}}</td>
	  <td>{{number_format(-$commodity['baselinedemand'])}}</td>
	  @if (-$commodity['baselinedemand'] > $commodity['baselinestock'])
	  <td><span class='deficit'>{{$commodity['baselinestock'] + $commodity['baselinedemand']}}</span></td>
	  @else
	  <td><span class='surplus'>{{$commodity['baselinestock'] + $commodity['baselinedemand']}}</span></td>
	  @endif
	  <td>{{$commodity['supplycycle']?number_format($commodity['supplycycle'],1):''}}</td>
	  <td>{{$commodity['demandcycle']?number_format($commodity['demandcycle'],1):''}}</td>
	  @if ($commodity['cycdemand'] === null || $commodity['cycstock'] === null)
	  <td></td>
	  @elseif ($commodity['cycdemand'] > $commodity['cycstock'])
	  <td><span class='deficit'>{{$commodity['cycstock'] - $commodity['cycdemand']}}</span></td>
	  @else
	  <td><span class='surplus'>{{$commodity['cycstock'] - $commodity['cycdemand']}}</span></td>
	  @endif
	  <td data-search='
		@foreach ($commodity['exported'] as $export)
		{{$export->name}}
		@endforeach
		  '>
		@foreach ($commodity['exported'] as $export)
		@include($export->icon)
		@endforeach
	  </td>
	  <td data-search='
		@foreach ($commodity['imported'] as $import)
		{{$import->name}}
		@endforeach
		  '>
		@foreach ($commodity['imported'] as $import)
		@include($import->icon)
		@endforeach
	  </td>
      <td title='{{$commodity['buyplace']}}'>{{number_format($commodity['buy'])}}</td>
	  <td title='{{$commodity['sellplace']}}'>{{number_format($commodity['sell'])}}</td>
	  <td>
		<a href="{{route('reserves.commodity.history', $commodity['id'])}}">
		  {{$commodity['name']}} History
		</a>
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
