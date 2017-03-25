@extends('layout/layout')

@section('title')
{{$faction->name}} - edit pending states
@endsection

@section('content')

<h2>Today {{$target->format("j F")}}</h2>
{!! Form::open(['route' => ['factions.update', $faction->id], 'method'=>'PUT']) !!}
<p>Last updated:
  @if ($latest)
  {{\App\Util::displayDate($latest)}}
  @else
  Never
  @endif
</p>
<p>If there are no pending states, tick 'None' only.</p>
@foreach ($states as $state)
<div>
  <input type='checkbox' name='pending[{{$state->id}}]' value='{{$state->id}}'
  @if ($pending->contains($state))
  checked='checked'
  @endif
  id='pending{{$state->id}}'>
  <label for='pending{{$state->id}}'>
	@include($state->icon)
	{{$state->name}}
  </label>
</div>
@endforeach

{!! Form::submit("Update pending states") !!}
{!! Form::token() !!}
{!! Form::close() !!}



@endsection
