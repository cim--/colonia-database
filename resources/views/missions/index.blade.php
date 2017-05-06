@extends('layout/layout')

@section('title')
Mission Types
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('missions.create')}}'>New type</a></p>
@endif

<p>Missions which have an effect on destination factions which is generally considered negative are highlighted with @include('missions/danger'). Think carefully before accepting one.</p>

<table data-page-length='50' class='table table-bordered datatable'>
  <thead>
	<tr>
	  <th>Type</th>
	  <th>Reputation</th>
	  <th>Source Influence</th>
	  <th>Source State</th>
      <th>Destination Influence</th>
	  <th>Destination State</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($missions as $mission)
	<tr
    @if ($mission->hasDestination &&
      (0 > $mission->destinationInfluenceMagnitude ||
      0 > $mission->destinationState->sign * $mission->destinationStateMagnitude))
	  class='mission-danger'
	  @endif
    >
      @if ($userrank > 1)
	  <td><a href="{{route('missions.edit', $mission->id)}}">{{$mission->type}}</a></td>
	  @else
	  <td>{{$mission->type}}</td>
	  @endif
	  <td data-sort="{{$mission->reputationMagnitude}}">
		{{App\Util::magnitude($mission->reputationMagnitude)}}
	  </td>
	  <td data-sort="{{$mission->sourceInfluenceMagnitude}}">
		{{App\Util::magnitude($mission->sourceInfluenceMagnitude)}}
	  </td>
	  <td data-sort="{{$mission->sourceState->name}}">
		@include($mission->sourceState->icon)
		{{$mission->sourceState->name}}
		{!! App\Util::sign($mission->sourceStateMagnitude) !!}
	  </td>
	  @if ($mission->hasDestination)
	  <td data-sort="{{$mission->destinationInfluenceMagnitude}}">
		{{App\Util::magnitude($mission->destinationInfluenceMagnitude)}}
		@if (0 > $mission->destinationInfluenceMagnitude)
		@include('missions/danger')
		@endif
	  </td>
	  <td data-sort="{{$mission->destinationState->name}}">
		@include($mission->destinationState->icon)
		{{$mission->destinationState->name}}
		{!! App\Util::sign($mission->destinationStateMagnitude) !!}
		@if (0 > $mission->destinationState->sign * $mission->destinationStateMagnitude)
		@include('missions/danger')
		@endif
	  </td>
	  @else
	  <td></td><td></td>
	  @endif
	</tr>
	@endforeach
  </tbody>
</table>


@endsection
