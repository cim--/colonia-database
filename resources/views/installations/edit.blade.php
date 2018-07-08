@extends('layout/layout')

@section('title')
{{$installation->installationclass->name}} Installation - edit
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($installation, ['route' => ['installations.update', $installation->id], 'method' => 'PUT']) !!}

@include('installations/form')

{!! Form::submit('Update installation') !!}

{!! Form::close() !!}
</div>

@endsection
