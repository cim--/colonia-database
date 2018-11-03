@extends('layout/layout')

@section('title')
Reserves at <a href="{{route('stations.show', $station->id)}}">{{$station->name}}</a>
@include('components.stateicons', ['states' => $station->currentStateList()])
@endsection
@section('headtitle')
Reserves at {{$station->name}}
@endsection

@section('content')

<p>Economy size:
  @if ($station->economysize)
  {{number_format($station->displayEconomysize())}}
  @else
  Unknown
  @endif
</p>
<p>Total estimated reserves: {{number_format($supply)}}</p>
<p>Total estimated demand: {{number_format($demand)}}</p>

<p>Last update: {{\App\Util::displayDate($reserves->first()->reserves->first()->created_at)}}</p>

@if ($station->currentStateList()->where('name', "Lockdown")->count() > 0)
<p><strong>Station is currently in Lockdown - commodity market unavailable.</strong> Table shows last known market state.</p>
@endif

<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th>Commodity</th>
	  <th>Status</th>
	  <th>Stock/Demand</th>
	  <th>Baseline Stock/Demand</th>
	  <th title='Allowing for the overall size of the station economy, how much does this produce/consume relative to other stations?'>Relative Production/Consumpion</th>
	  <th>Price</th>
	  <th>History</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($reserves as $reserve)
	<tr>
	  <td>
		<a href="{{route('reserves.commodity.reference', [$reserve->id, $station->id])}}">
		  {{$reserve->displayName()}}
		</a>
	  </td>
	  <td>
		@if ($reserve->reserves->first()->reserves > 0)
		Exports
		@else
		Imports
		@endif
	  </td>
	  <td>
		@if ($reserve->reserves->first()->reserves > 0)
		<span class='surplus'>{{$reserve->reserves->first()->reserves}}</span>
		@else
		<span class='deficit'>{{$reserve->reserves->first()->reserves}}</span>
		@endif
	  </td>
	  <td>
		@include('components/surplusdeficit', ['value' => $station->baselinestocks->where('commodity_id', $reserve->id)->first()])
	  </td>
	  @include('components/intensity', ['baseline' => $station->baselinestocks->where('commodity_id', $reserve->id)->first(), 'stats' => $reserve->commoditystat])
	  <td>
		{{$reserve->reserves->first()->price}}
	  </td>
	  <td>
	    <a href='{{route('stations.showtradehistory', ['station'=>$station->id, 'commodity' =>$reserve->id])}}'>{{$reserve->displayName()}} History</a>
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
