@extends('layout/layout')

@section('title', 'Module availability: '.$moduletype->description)

@section('content')

@include('outfitting/engineertext')

<ul class='compact'>
@foreach ($modules as $module)
<li><a href='{{route('outfitting.module', [$moduletype->id, $module->id])}}'>{{$module->displayName()}}</a></li>
@endforeach
</ul>

@endsection
