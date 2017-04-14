@extends('layout/layout')

@section('title', 'System Map')

@section('content')

<p><strong>Projection</strong>: XZ ; <strong>Links</strong>: 15 LY ; <strong>Colour</strong>: Phase</p>

<canvas id='cdbmap' width='1000' height='1000'></canvas>
<script type='text/javascript'>CDBMap.Init(
  [
  @foreach ($systems as $system)
  {
  'name' : "{{$system->displayName()}}",
  'x' : {{$system->coloniaCoordinates()->x}},
  'y' : {{$system->coloniaCoordinates()->y}},
  'z' : {{$system->coloniaCoordinates()->z}},
  'phase' : {{$system->phase->sequence}},
  'population' : {{$system->population}}
  }
  @if (!$loop->last) , @endif
  @endforeach
  ]
  )</script>

@endsection
