@extends('layout/layout')

@section('title', 'State Effects: '.$state->name)

@section('content')

@include('components.unstableanalysis')
    
<table class='table table-bordered datatable' data-page-length='25'>
  <thead>
	<tr>
	  <th>Commodity</th>
	  <th>Supply Quantity</th>
	  <th>Supply Price</th>
	  <th>Demand Quantity</th>
	  <th>Demand Price</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($commodities as $commodity)
	<tr>
	  <td>
		<a href='{{route('effects.commodity', $commodity->id)}}'>
		  {{$commodity->displayName()}}
		</a>
	  </td>
	  @include('trade/effectsrow', ['effect' => $state->effects->filter(function ($e) use ($commodity) {
	  return $e->commodity_id == $commodity->id;
	  })->first()])
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
