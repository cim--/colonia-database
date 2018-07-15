@extends('layout/layout')

@section('title')
{{$site->summary}} - edit
@endsection

@section('content')

<div class='modelform'>
{!! Form::model($site, ['route' => ['sites.update', $site->id], 'method' => 'PUT']) !!}

@include('sites/form')

{!! Form::submit('Update site') !!}

{!! Form::close() !!}
</div>

@endsection
