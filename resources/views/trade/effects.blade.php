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

{{--
<table class='table table-bordered'>
  <thead>
	<tr>
	  <th>Economy</th>
	  @foreach ($states as $state)
	  <th>
		@include($state->icon)
	  </th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($economies as $economy)
	<tr>
	  <td>
		{{$economy->name}}
		@include($economy->icon)
	  </td>
	  @foreach ($states as $state)
	  <td>
		@include('trade.effectratio', ['ratio' => $economy->tradePriceRatio($state)])
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
--}}


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
