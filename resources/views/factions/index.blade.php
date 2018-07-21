@extends('layout/layout')

@section('title')
Factions
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('factions.create')}}'>New faction</a></p>
@endif

<p><a href='{{route('factions.ethos')}}'>Summary of faction governments</a></p>
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>Government</th>
      <th>Current States</th>
	  <th>Pending States</th>
	  <th>Player?</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($factions as $faction)
	<tr>
	  <td><a href='{{route("factions.show", $faction->id)}}'>{{$faction->name}}</a></td>
      <td>
		@include($faction->government->icon)
		{{$faction->government->name}}
		@if ($faction->ethos && $faction->ethos->name != "Unknown")
		({{$faction->ethos->name}})
		@endif
	  </td>
	  @include('factions.statecell', ['states' => $faction->currentStates()])
	  @include('factions.statecell', ['states' => $faction->states])
      <td>
		@if ($faction->player)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
