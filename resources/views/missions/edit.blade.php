@extends('layout/layout')

@section('title')
Edit {{$missiontype->type}}
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($missiontype, ['route' => ['missions.update', $missiontype->id], 'method' => 'PUT']) !!}

@include('missions/form')

{!! Form::submit('Update mission') !!}

{!! Form::close() !!}
</div>


@endsection
