@extends('layout/layout')

@section('title', 'Distances and Expansion Predictor')

@section('content')

<table class='table table-bordered' id='distancegrid'>
  <thead>
	<tr>
	  <td rowspan='2' colspan='3'></td>
	  @foreach ($systems as $system)
	  <th class='phase{{$system->phase->sequence}}'>
		<div>{{$system->displayName()}}</div>
	  </th>
	  @endforeach
	</tr>
	<tr>
	  @foreach ($systems as $system)
	  <td class='phase{{$system->phase->sequence}}'>
		{{count($presents[$system->id])}}
	  </td>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($systems as $system)
	<tr>
	  <td class='controller phase{{$system->phase->sequence}}'>
		@if ($system->inhabited())
		<span title='{{$system->controllingFaction()->name}}'>
		  {{$system->controllingFaction()->abbreviation()}}
		</span>
		@endif
	  </td>
	  <td class='controller-size phase{{$system->phase->sequence}}'>
		@if ($system->inhabited())
		{{$system->controllingFaction()->latestSystems()->count()}}
		@endif
	  </td>
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
