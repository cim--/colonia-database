@extends('layout/layout')

@section('title')
New Megaship
@endsection

@section('content')

{!! Form::open(['route' => 'megaships.store']) !!}

@include('megaships/form')

{!! Form::submit('Create megaship') !!}

{!! Form::close() !!}
    
@endsection
