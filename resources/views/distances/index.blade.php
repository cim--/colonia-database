@extends('layout/layout')

@section('title', 'Distances and Expansion Predictor')

@section('content')

<table class='table table-bordered' id='distancegrid'>
  <thead>
	<tr>
	  <td></td>
	  @foreach ($systems as $system)
	  <th class='phase{{$system->phase->sequence}}'>
		<div>{{$system->displayName()}}</div>
	  </th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($systems as $system)
	<tr>
	  <th class='phase{{$system->phase->sequence}}'>
		{{$system->displayName()}}
	  </th>
	  @foreach ($systems as $system2)
	  @include('distances/cell', ['cell' => $grid[$system->id][$system2->id]])
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>


@endsection
