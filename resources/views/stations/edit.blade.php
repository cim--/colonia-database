@extends('layout/layout')

@section('title')
{{$station->name}} - edit
@endsection

@section('content')


@if ($userrank > 1)
<div class='modelform'>
{!! Form::model($station, ['route' => ['stations.update', $station->id], 'method' => 'PUT']) !!}

@include('stations/form')

{!! Form::submit('Update station') !!}

{!! Form::hidden('editmain', 1) !!}
{!! Form::close() !!}
</div>
@endif


@endsection
