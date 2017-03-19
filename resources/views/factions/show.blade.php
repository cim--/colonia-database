@extends('layout/layout')

@section('title')
{{$faction->name}}
@endsection

@section('content')

<div class='row'>
  <div class='col-sm-12'>
    @if ($faction->player)
    <p>Player faction</p>
    @endif
	<p>Government: {{$faction->government->name}}</p>
  </div>
</div>

<div class='row'>
  <div class='col-sm-6'>
	<h2>Stations</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>Planet</th><th>Type</th></tr>
	  </thead>
	  <tbody>
	  </tbody>
	</table>
  </div>
  <div class='col-sm-6'>
	<h2>Systems</h2>
	<table class='table table-bordered datatable'>
	  <thead>
		<tr><th>Name</th><th>Influence</th><th>State</th></tr>
	  </thead>
	  <tbody>
	  </tbody>
	</table>
  </div>
</div>

@endsection
