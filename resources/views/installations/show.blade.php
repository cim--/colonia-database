@extends('layout/layout')

@section('title')
{{$installation->installationclass->name}} Installation
@if ($installation->name)
({{$installation->name}})
@endif
@endsection

@section('content')
@include('components/trackbox', ['domain' => 'installations', 'id' => $installation->id])
@if ($userrank > 1)
<a class='edit' href='{{route('installations.edit', $installation->id)}}'>Update</a>
@endif

<div><strong>Location:</strong>
  <a href='{{route('systems.show', $installation->system_id)}}'>
	{{$installation->system->displayName()}}
  </a> {{$installation->planet}}
</div>
<div><strong>Constructed:</strong> {{App\Util::displayDate($installation->constructed)}}</div>
<div><strong>Owner:</strong>
  @if ($installation->faction_id)
  <a href='{{route('factions.show', $installation->faction_id)}}'>
    {{$installation->faction->name}}
  </a>
  @else
  Not Known
  @endif
</div>

@if ($installation->cargo)
<p><strong>Typical cargo:</strong> {{$installation->cargo}}</p>
@endif

@if ($installation->satellites)
<p><strong>Satellites are present</strong></p>
@endif
@if ($installation->trespasszone)
<p><strong>An exclusion zone is defined</strong></p>
@endif

@endsection
