@extends('layout/layout')

@section('title', 'Trade specialisation')

@section('content')

    {!! Form::open(['route' => ['specialisation.hybrid'], 'method' => 'GET']) !!}
<ul class='compact'>
  @foreach ($economies as $economy)
  <li>
      @include($economy->icon)
      {{$economy->name}}
      {!! Form::number('eco'.$economy->id, $weights[$economy->id], ['min' => 0, 'max' => 10, 'step' => 0.01]) !!}
  </li>
  @endforeach
</ul>
<div>
    {!! Form::label('mode', 'Calculation mode') !!}
    {!! Form::select('mode', ['t' => 'Traditional economy', 'c' => 'Colonisation economy'], $mode) !!}
</div>
{!! Form::submit('Calculate') !!}
{!! Form::close() !!}

<p>This table shows the expected ranges for supply and demand for a station with the above economy proportions, per economic unit. Positive values represent supply, negative values represent demand. A political state of None is assumed.</p>

<p>The 'Contraband' economy is the 'new' Service type used by newly-colonised systems, while the 'Service' economy is the traditional type used rarely by older systems.</p>

@if ($primaryeco)
    <p>Using colonised station algorithm which protects exports of the top two economies: {{ $primaryeco->name }}@if ($secondaryeco)
	and {{ $secondaryeco->name }}
    @endif. Ranges are not yet well understood for these commodities.
    </p>
@endif

<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th>Category</th>
	  <th>Name</th>
	  <th>Exported By</th>
	  <th>Imported By</th>
	  <th>Very Low</th>
	  <th>Low</th>
	  <th>Normal</th>
	  <th>High</th>
	  <th>Very High</th>
	  <th>Outcome</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($commodities as $commodity)
	@if (isset($commodity->supplystats) && ($commodity->commoditystat->supplymed || $commodity->commoditystat->demandmed))
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
	  <td data-search="
			   @foreach ($commodity->imports ?? [] as $economy)
			   {{ $economy->name }}
			   @endforeach
			   ">
	      @foreach ($commodity->imports ?? [] as $economy)
		  @include($economy->icon)
	      @endforeach
	  </td>
	  @if ($commodity->exportprotection > 0)
	      @foreach ($commodity->supplystats as $pos)
		  <td style="background: #eee;">{{ number_format($pos, 2) }}</td>
	      @endforeach
	  @else
	      @foreach ($commodity->supplystats as $pos)
		  <td>{{ number_format($pos, 2) }}</td>
	      @endforeach
	  @endif
	  @if ($commodity->exportprotection > 1)
	      <td data-sort="-2">Primary Protected Export</td>
	  @elseif ($commodity->exportprotection > 0)
	      <td data-sort="-1">Secondary Protected Export</td>
	  @elseif ($commodity->supplystats[0] > 0)
	      <td data-sort="0">Export</td>
	  @elseif ($commodity->supplystats[1] > 0)
	      <td data-sort="1">Probable Export</td>
	  @elseif ($commodity->supplystats[2] > 0)
	      <td data-sort="2">Possible Export</td>
	  @elseif ($commodity->supplystats[3] > 0)
	      <td data-sort="3">Possible Import</td>
	  @elseif ($commodity->supplystats[4] > 0)
	      <td data-sort="4">Probable Import</td>
	  @else
	      <td data-sort="5">Import</td>
	  @endif
	</tr>
	@endif
	@endforeach
  </tbody>
</table>
@endsection
