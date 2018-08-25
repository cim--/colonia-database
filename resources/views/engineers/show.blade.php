@extends('layout/layout')

@section('title')
{{$engineer->name}}
@endsection

@section('content')
@include('components/trackbox', ['domain' => 'engineers', 'id' => $engineer->id])
@if ($userrank > 1)
<a class='edit' href='{{route('engineers.edit', $engineer->id)}}'>Update</a>
@endif

<div>
  <strong>Location:</strong>
  <a href='{{route('systems.show',$engineer->station->system_id)}}'>{{$engineer->station->system->displayName()}}</a>
  {{$engineer->station->planet}}
  <a href='{{route('stations.show',$engineer->station_id)}}'>{{$engineer->station->name}}</a>
</div>

<div>
  <strong title='Actions needed to receive initial communications'>Discovery:</strong>
  {{$engineer->discovery}}
</div>
<div>
  <strong title='Actions needed to receive invitation to visit'>Invitation:</strong>
  {{$engineer->invitation}}
</div>
<div>
  <strong title='Actions needed to get access to engineering'>Access:</strong>
  {{$engineer->access}}
</div>

<h2>Blueprints</h2>

<ul id='blueprintlist'>
  @foreach ($engineer->blueprints as $blueprint)
  <li>
	@include ('components.blueprint')
	<a href='{{route('outfitting.moduletype', $blueprint->moduletype)}}'>
	  {{$blueprint->moduletype->description}}
	</a>
  </li>
  @endforeach
</ul>
@endsection
