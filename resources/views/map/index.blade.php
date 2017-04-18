@extends('layout/layout')

@section('title', 'System Map')

@section('content')

<p>
  <strong>Projection</strong>: <select id='mapctrlprojection' name='projection'>
	<option selected='selected'>XZ</option>
	<option>XY</option>
	<option>ZY</option>
  </select> ;
  <strong>Links</strong>: 15 LY ;
  <strong>Colour</strong>: Phase ;
  <strong>Size</strong>: Population
</p>

<div id='cdbmapcontainer'>
  <canvas id='cdbmap' width='1200' height='1000'></canvas>
  <script type='text/javascript'>CDBMap.Init(
  [
  @foreach ($systems as $system)
  {
  'name' : "{{$system->displayName()}}",
  @if ($projection == 'XZ')
  'x' : {{$system->coloniaCoordinates()->x}},
  'z' : {{$system->coloniaCoordinates()->z}},
  'y' : {{$system->coloniaCoordinates()->y}},
  @elseif ($projection == 'XY')
  'x' : {{$system->coloniaCoordinates()->x}},
  'z' : {{$system->coloniaCoordinates()->y}},
  'y' : {{$system->coloniaCoordinates()->z}},
  @else 
  'x' : {{$system->coloniaCoordinates()->z}},
  'z' : {{$system->coloniaCoordinates()->y}},
  'y' : {{$system->coloniaCoordinates()->x}},
  @endif
  'phase' : {{$system->phase->sequence}},
  'population' : {{$system->population}}
  }
  @if (!$loop->last) , @endif
  @endforeach
  ]
  )
  </script>
</div>

@endsection
