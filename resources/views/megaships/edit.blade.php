@extends('layout/layout')

@section('title')
{{$megaship->displayName()}} - edit
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($megaship, ['route' => ['megaships.update', $megaship->id], 'method' => 'PUT']) !!}

@include('megaships/form')

{!! Form::submit('Update megaship') !!}

{!! Form::close() !!}
</div>

@endsection
