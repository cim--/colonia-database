@extends('layout/layout')

@section('title')
Mission Types
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('missions.create')}}'>New type</a></p>
@endif

<table class='table table-bordered datatable'>
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
	<tr>
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
	  </td>
	  <td data-sort="{{$mission->destinationState->name}}">
		@include($mission->destinationState->icon)
		{{$mission->destinationState->name}}
		{!! App\Util::sign($mission->destinationStateMagnitude) !!}
	  </td>
	  @else
	  <td></td><td></td>
	  @endif
	</tr>
	@endforeach
  </tbody>
</table>


@endsection
