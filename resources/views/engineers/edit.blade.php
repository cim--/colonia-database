@extends('layout/layout')

@section('title')
{{$engineer->name}} - edit
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($engineer, ['route' => ['engineers.update', $engineer->id], 'method' => 'PUT']) !!}

@include('engineers/form')

{!! Form::submit('Update engineer') !!}

{!! Form::close() !!}
</div>

@endsection
