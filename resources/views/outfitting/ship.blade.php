@extends('layout/layout')

@section('title')
Availability of {{$ship->name}}
@endsection

@section('content')

@if ($ship->stations->count())
<p>The {{$ship->name}} is available at the following shipyards:</p>
<ul>
@foreach ($ship->stations->sortBy('name') as $station)
<li>
  <a href="{{route('stations.show', $station->id)}}">
	{{$station->name}}
  </a>
</li>
@endforeach
</ul>
@else
<p>The {{$ship->name}} is not currently available in the Colonia region.</p>
@endif

@endsection
