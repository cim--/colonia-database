@extends('layout/layout')

@section('title')
Users
@endsection

@section('content')

{!! Form::open(['route' => 'users.update']) !!}

@foreach ($users as $user)
<div><input type='checkbox' name='user{{$user->id}}' value='1'
@if ($user->rank > 0)
checked='checked'
@endif
> {{$user->name}}</div>
@endforeach
{!! Form::submit('Update users') !!}

{!! Form::close() !!}

@endsection
