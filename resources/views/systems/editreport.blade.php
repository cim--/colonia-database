@extends('layout/layout')

@section('title')
{{$system->displayName()}} - edit reports
@endsection

@section('content')

<h2>Edit reports</h2>
{!! Form::open(['route' => ['systems.updatereport', $system->id], 'method'=>'PUT']) !!}
<p>Reports are available in the local galnet at a station. In low-activity systems, there may be no Crime or Bounties report - in this case, put zero. For crime and bounties, enter the number of credits, not the number of incidents.</p>
<table class='table table-bordered'>
  <thead>
	<tr><th>Date</th><th>Traffic Total</th><th>Crime</th><th>Bounties (Cr.)</th></tr>
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
