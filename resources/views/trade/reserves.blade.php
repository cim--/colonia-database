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
  tonnes excluding mined/salvaged/etc. goods).<br>Total demand: {{number_format($demandtotal)}} tonnes<br>Total reserves: {{number_format($stocktotal)}} tonnes
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
	  <th>Reserves</th>
	  <th>Demand</th>
	  <th>Status</th>
	  <th>Surplus</th>
	  <th>Exported</th>
	  <th>Imported</th>
	  <th>Buy</th>
	  <th>Sell</th>
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
	  <td>Deficit</td>
	  <td><span class='deficit'>{{$commodity['stock'] - $commodity['demand']}}</span></td>
	  @else
	  <td>Surplus</td>
	  <td><span class='surplus'>{{$commodity['stock'] - $commodity['demand']}}</span></td>
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
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
