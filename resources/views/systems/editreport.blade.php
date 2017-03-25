@extends('layout/layout')

@section('title')
{{$system->displayName()}} - edit reports
@endsection

@section('content')

<h2>Edit reports</h2>
{!! Form::open(['route' => ['systems.updatereport', $system->id], 'method'=>'PUT']) !!}
<table class='table table-bordered'>
  <thead>
	<tr><th>Date</th><th>Traffic Total</th><th>Crime</th><th>Bounties</th></tr>
  </thead>
  <tbody>
	@if ($latest)
	<tr>
	  <td>
		{{ \App\Util::displayDate($latest->date) }}
	  </td>
	  <td>
		{{$latest->traffic}}
	  </td>
	  <td>
		{{$latest->crime}}
	  </td>
	  <td>
		{{$latest->bounties}}
	  </td>
	</tr>
	@else
	<tr>
	  <td colspan='4'>No previous reports logged</td>
	</tr>
	@endif
	<tr>
	  <td>
		{{ \App\Util::displayDate($target) }}
	  </td>
	  <td>
		{!! Form::text("traffic", '') !!}
	  </td>
	  <td>
		{!! Form::text("crime", '') !!}
	  </td>
	  <td>
		{!! Form::text("bounties", '') !!}
	  </td>
	</tr>
  </tbody>
</table>
{!! Form::submit("Update reports") !!}
{!! Form::token() !!}
{!! Form::close() !!}



@endsection
