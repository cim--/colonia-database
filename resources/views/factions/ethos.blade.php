@extends('layout/layout')

@section('title')
Faction government types
@endsection

@section('content')

<p>Colonia has many government types which do not exist in the Sol bubble, though they have been recorded as a Sol bubble type by Universal Cartographics. Types which exist in the Sol bubble are highlighted in yellow, while types unique to Colonia are highlighted in purple.</p>

<table class='table table-bordered' id='ethosgrid'>
  <thead>
	<tr>
	  <td></td>
	  @foreach ($ethoses as $ethos)
	  <th scope='col'>{{$ethos->name}}</th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($governments as $government)
	<tr>
	  <th scope='row'>
		@include($government->icon)
		{{$government->name}}
	  </th>
	  @foreach ($ethoses as $ethos)
	  @include('factions.ethoscell', ['data' => $grid[$government->id][$ethos->id]])
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>

    
@endsection
