@extends('layout/layout')

@section('title', 'Colonia Region System Database')

@section('content')

<div class='row'>
  <div class='col-sm-6'>
	<h2>Systems</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Phase</th><th>Name</th><th>Economy</th></tr>
	  </thead>
	  <tbody>
		@foreach ($systems as $system)
		<tr>
		  <td data-sort='{{$system->phase->sequence}}'>{{$system->phase->name}}</td>
		  <td><a href="{{route('systems.show', $system->id)}}">{{$system->displayName()}}</a></td>
		  @if ($system->economy)
		  <td>{{$system->economy->name}}</td>
		  @else
		  <td>None</td>
		  @endif
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
  <div class='col-sm-6'>
	<h2>Factions</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>Government</th><th>Player?</th></tr>
	  </thead>
	  <tbody>
		@foreach ($factions as $faction)
		<tr>
		  <td>{{$faction->name}}</td>
		  <td>{{$faction->government->name}}</td>
		  <td>{{$faction->player ? 'Yes' : 'No'}}</td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>
</div>



@endsection
