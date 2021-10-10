@extends('layout/layout')

@section('title')
Logistics Planner - Report
@endsection

@section('content')
    
<h2>Shipping to {{$destination->name}}, {{$destination->system->displayName()}}</h2>
<p><strong>Target</strong>: {{number_format($volume)}} tonnes in {{$duration}} days.</p>
<ul>
  @foreach ($commodities as $commodity)
      <li>{{$commodity->displayName()}} - production cycle {{number_format($commodity->supplycycle/86400, 1)}} days
	  @if ($commodity->cycleestimate == "Supply" || $commodity->cycleestimate == "Both")
	      (low-quality estimate)
	  @endif
      </li>
  @endforeach
</ul>

<p>Current stock is {{number_format($total)}} tonnes and current estimated restock rates are {{number_format($restock)}} tonnes per day. Optimally, a total of {{number_format($bestcase)}} tonnes can therefore be gathered, which
  @if ($bestcase > $volume)
  <strong>exceeds the target</strong>
  @else
  <strong>is short of the target</strong>
  @endif
</p>

<table class='table table-bordered datatable' data-page-length='25' data-order='[[3, "asc"]]'> 
  <thead>
	<tr>
	  <th>Commodity</th>
	  <th>System</th>
	  <th>Station</th>
	  <th>Distance (LY)</th>
	  <th>Distance (Ls)</th>
	  <th>Large Pad?</th>
	  <th>Reported stock</th>
	  <th>Last report</th>
	  <th>Capacity</th>
	  <th>Stock fullness</th>
	  <th>Daily restock</th>
	  <th>Recommendation</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($options as $option)
	<tr>
	  <td>{{$option['commodity']->displayName()}}</td>
	  <td>
		<a href='{{route('systems.show', $option['station']->system_id)}}'>
		  {{$option['station']->system->displayName()}}
		</a>
	  </td>
	  <td>
		<a href='{{route('stations.show', $option['station']->id)}}'>
		  {{$option['station']->name}}
		</a>
	  </td>
	  <td>{{number_format($option['distance'],2)}}</td>
	  <td>{{number_format($option['station']->distance)}}</td>
	  <td>
		@if ($option['station']->stationclass->hasLarge)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	  @if ($option['reserves'])
	      <td>{{number_format($option['reserves']->reserves)}}</td>
	      <td data-sort="{{$option['reserves']->created_at->timestamp}}">{{$option['reserves']->created_at->diffForHumans()}}</td>
	  @else
	      <td>-</td>
	      <td data-sort="0">-</td>
	  @endif
	  @if (isset($option['sbaseline']))
	  <td>{{number_format($option['sbaseline'])}}</td>
	  <td>{{number_format($option['fullness']*100)}}%</td>
	  <td>{{number_format($option['regen'])}}</td>
	  @else
	  <td>-</td>
	  <td>-</td>
	  <td>-</td>
	  @endif
	  <td data-sort='{{$option['score']}}' class='logistics-recommendation-{{$option['score']}}'>{{$option['recommendation']}}</td>
	</tr>
	@endforeach
  </tbody>
</table>
	  


@endsection
