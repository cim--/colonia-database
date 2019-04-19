@extends('layout/layout')

@section('title', 'State Effects')

@section('content')

<p>This section shows the effects on commodity prices and supply/demand levels by state. Data for uncommon states is more likely to be incomplete or inaccurate.</p>

<h2>Effects of States</h2>
<ul class='compact'>
  @foreach ($states as $state)
  <li>
	@if ($state->name == "Lockdown")
	  {{$state->name}}
	  @include($state->icon)
	  (no market)
	@else
	<a href="{{route('effects.state', $state->id)}}">
	  {{$state->name}}
	  @include($state->icon)
	</a>
	@endif
  </li>
  @endforeach
</ul>

<p>This table shows the ratios between supply and demand for all commodities. 100% is equal supply and demand, with greater being more supply. Ratios are shown for tonnage and for credit value of goods.</p>
<table class='table table-bordered'>
  <thead>
	<tr>
	  <th scope='col'>Economy</th>
	  @foreach ($states as $state)
	  @if ($state->name != "Lockdown")
	  <th scope='col'>
		@include($state->icon)
		{{$state->name}}
	  </th>
	  @endif
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($economies as $economy)
	<tr>
	  <th scope='row'>
		{{$economy->name}}
		@include($economy->icon)
	  </th>
	  @foreach ($states as $state)
	  @if ($state->name != "Lockdown")
	  <td>
		@if (isset($balances[$economy->id][$state->id]))
		@include('trade.effectratio', ['ratio' => $balances[$economy->id][$state->id]])
		@endif
	  </td>
	  @endif
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>


<h2>Effects on Commodities</h2>
<ul class='compact'>
  @foreach ($commodities as $commodity)
  <li>
	<a href="{{route('effects.commodity', $commodity->id)}}">
	  {{$commodity->displayName()}}
	</a>
  </li>
  @endforeach
</ul>
    
@endsection
