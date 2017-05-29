@extends('layout/layout')

@section('title', 'Colonia Region History Log')

@section('content')

{!! Form::open(['route' => 'history.store']) !!}
<h2>System Events</h2>
<table>
  <tr>
	<td>
	  {!! Form::date('date', \App\Util::tick()->format("Y-m-d")) !!}
	</td>
	<td>
	  {!! Form::select('faction', $factions) !!}
	</td>
	<td>
	  {!! Form::text('description') !!}
	</td>
	<td>
	  {!! Form::select('system', $systems) !!}
	</td>
	<td>
	  {!! Form::submit('Create Event') !!}
	</td>
  </tr>
</table>
{!! Form::close() !!}
    
{!! Form::open(['route' => 'history.store']) !!}
<h2>Station Events</h2>
<table>
  <tr>
	<td>
	  {!! Form::date('date', \App\Util::tick()->format("Y-m-d")) !!}
	</td>
	<td>
	  {!! Form::select('faction', $factions) !!}
	</td>
	<td>
	  {!! Form::text('description') !!}
	</td>
	<td>
	  {!! Form::select('station', $stations) !!}
	</td>
	<td>
	  {!! Form::submit('Create Event') !!}
	</td>
  </tr>
</table>
{!! Form::close() !!}

    
@endsection
