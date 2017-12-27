@extends('layout/layout')

@section('title', 'State Effects: '.$commodity->displayName())

@section('content')

<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th>State</th>
	  <th>Supply Quantity</th>
	  <th>Supply Price</th>
	  <th>Demand Quantity</th>
	  <th>Demand Price</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($states as $state)
	@if ($state->name != "Lockdown")
	<tr>
	  <td>
		<a href='{{route('effects.state', $state->id)}}'>
		  {{$state->name}}
		  @include($state->icon)
		</a>
	  </td>
	  @include('trade/effectsrow', ['effect' => $commodity->effects->filter(function ($e) use ($state) {
	  return $e->state_id == $state->id;
	  })->first()])
	</tr>
	@endif
	@endforeach
  </tbody>
</table>
    
@endsection
