@extends('layout/layout')

@section('title')
New Site
@endsection

@section('content')

{!! Form::open(['route' => 'sites.store']) !!}

@include('sites/form')

{!! Form::submit('Create site') !!}

{!! Form::close() !!}
    
@endsection
