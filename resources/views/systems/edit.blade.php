@extends('layout/layout')

@section('title')
{{$system->displayName()}} - edit influence
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-6'>
	<h2>Yesterday</h2>
    <table class='table table-bordered'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>States</th></tr>
	  </thead>
	  <tbody>
		@foreach ($yesterday as $faction)
		<tr>
		  <td>{{$faction->faction->name}}</td>
		  <td>{{number_format($faction->influence, 1)}}</td>
		  <td>
			@foreach ($faction->states as $state)
			{{$state->name}}
			@endforeach
		  </td>
		  <td>
			{{$faction->happinessString()}}
		  </td>
		</tr>
		@endforeach
	  </tbody>
	</table>
  </div>

  <div class='col-sm-6'>
	<h2>Today {{$target->format("j F")}}</h2>
	{!! Form::open(['route' => ['systems.update', $system->id], 'method'=>'PUT']) !!}
    <table class='table table-bordered'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tbody>
		@foreach ($today as $idx => $faction)
		<tr>
		  <td>
			{!! Form::select("faction[$idx]", $factions, $faction->faction->id) !!}
		  </td>
		  <td>
			{!! Form::number("influence[$idx]", $faction->influence, ['min' => 0, 'max' => 100, 'step' => 0.1]) !!}
		  </td>
		  <td>
			{!! Form::select("state[$idx][]", $states, $faction->states->pluck('id'), ['multiple' => 'multiple', 'size' => 3]) !!}
		  </td>
		  <td>
			{!! Form::select("happiness[$idx]", $happinesslevels, $faction->happiness) !!}
		  </td>
		</tr>
		@endforeach
		@for ($idx = count($today); 8 > $idx ; $idx++)
		<tr>
		  <td>
			{!! Form::select("faction[$idx]", $factions, 0) !!}
		  </td>
		  <td>
			{!! Form::number("influence[$idx]", 0, ['min' => 0, 'max' => 100, 'step' => 0.1]) !!}
		  </td>
		  <td>
			{!! Form::select("state[$idx][]", $states, [],  ['multiple' => 'multiple', 'size' => 3]) !!}
		  </td>
		  <td>
			{!! Form::select("happiness[$idx]", $happinesslevels, 2) !!}
		  </td>
		</tr>
        @endfor
	  </tbody>
	</table>
	{!! Form::submit("Update influence") !!}
	{!! Form::token() !!}
	{!! Form::close() !!}
  </div>


</div>

@if ($userrank > 1)
<div class='modelform'>
{!! Form::model($system, ['route' => ['systems.update', $system->id], 'method' => 'PUT']) !!}

@include('systems/form')

{!! Form::submit('Update system') !!}

{!! Form::hidden('editmain', 1) !!}
{!! Form::close() !!}
</div>
@endif


@endsection
