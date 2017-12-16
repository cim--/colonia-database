@extends('layout/layout')

@section('title', 'Trade Helper')

@section('content')


{!! Form::open(['route' => 'trade.search', 'method' => 'GET']) !!}

<p>Select a reference station and a desired economy/state combination to trade with. (Selecting no states or economies is the same as selecting all of them)</p>
<div id='tradesearch'>
<div>
{!! Form::label('reference', 'Reference Station') !!}
{!! Form::select('reference', \App\Util::selectMap($stations), $reference?$reference->id:null) !!}
</div>
<div>
  <h2>Economies</h2>
  <div class='row'>
	@foreach ($economies as $economy)
	<div class='col-sm-3'>
	  {!! Form::checkbox('e['.$economy->id.']', $economy->id, isset($eparam[$economy->id]), ['id' => 'economy'.$economy->id])  !!}
	  @include($economy->icon)
	  {!! Form::label('economy'.$economy->id, $economy->name) !!}
	</div>
  @endforeach
  </div>
</div>
<div>
  <h2>States</h2>
  <div class='row'>
	@foreach ($states as $state)
	<div class='col-sm-3'>
	  {!! Form::checkbox('s['.$state->id.']', $state->id, isset($sparam[$state->id]), ['id' => 'state'.$state->id])  !!}
	  @include($state->icon)
	  {!! Form::label('state'.$state->id, $state->name) !!}
	</div>
	@endforeach
  </div>
</div>
{!! Form::submit('Search') !!}
</div>

</div>


{!! Form::close() !!}

@if ($search !== null)

<div id='traderesults'>
  <table class='table table-bordered datatable'>
	<thead>
	  <tr><th>Distance (LY)</th><th>System</th><th>Station</th><th>Type</th><th>Economy</th><th>Faction</th></tr>
	</thead>
	<tbody>
	  @foreach ($search as $result)
	  <tr>
		<td>{{number_format($reference->system->distanceTo($result->system), 2)}}</td>
		<td>
		  <a href='{{route("systems.show", $result->system->id)}}'>
			{{$result->system->displayName()}}
		  </a>
		</td>
		<td>
		  <a href='{{route("stations.show", $result->id)}}'>
			{{$result->name}}
		  </a>
		</td>
		<td>
		  {{$result->stationclass->name}}
		</td>
		<td>
		  @include($result->economy->icon)
		  {{$result->economy->name}}
		</td>
		<td>
		  @if($result->stateicon)
		  @include($result->stateicon)
		  @endif
		  <a href='{{route("factions.show", $result->faction->id)}}'>
			{{$result->faction->name}}
		  </a>
		</td>
	  </tr>
	  @endforeach
	</tbody>
  </table>
</div>

@endif

@endsection
