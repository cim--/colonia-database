@extends('layout/layout')

@section('title')
Systems
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('systems.create')}}'>New system</a></p>
@endif
    
<table class='table table-bordered datatable' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Phase</th>
	  <th>Name</th>
	  <th>Population</th>
	  <th>Economy</th>
	  <th>Government</th>
	  <th>Controlling Faction</th>
      <th>Locations</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($systems as $system)
	<tr>
	  <td data-sort='{{$system->phase->sequence}}'>{{$system->phase->name}}</td>
	  <td><a href="{{route('systems.show', $system->id)}}">{{$system->displayName()}}</a></td>
	  @if ($system->inhabited())
	  <td>{{number_format($system->population, 0)}}</td>
      <td>
		@include($system->economy->icon)
		{{$system->economy->name}}
	  </td>
      <td>
		@include($system->controllingFaction()->government->icon)
		{{$system->controllingFaction()->government->name}}
	  </td>
	  <td><a href="{{route('factions.show', $system->controllingFaction()->id)}}">{{$system->controllingFaction()->name}}</a></td>
	  @else
	  <td>0</td>
	  <td></td>
	  <td></td>
	  <td></td>
	  @endif
	  <td data-search='
		@foreach ($system->facilities as $facility)
{{$facility->name}}
@endforeach
'>
		@foreach ($system->facilities as $facility)
		@if (!$facility->pivot->enabled)<span class='facility-disabled'>@endif
		  @include ($facility->icon)
		  @if (!$facility->pivot->enabled)</span>@endif
  		@endforeach
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
