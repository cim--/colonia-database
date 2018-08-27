@extends('layout/layout')

@section('title')
Systems
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('systems.create')}}'>New system</a></p>
@endif
    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"],[1, "asc"]]'>
  <thead>
	<tr>
	  <th>Phase</th>
	  <th>Name</th>
	  <th>Population</th>
	  <th>Economy Size</th>
	  <th>Economy Type</th>
	  <th>Government</th>
	  <th>Controlling Faction</th>
      <th>Exploration Value</th>
      <th>Locations</th>
      <th>Installations</th>
	  <th>Megaships</th>
	  <th>Sites</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($systems as $system)
	<tr>
	  <td data-sort='{{$system->phase->sequence}}'>{{$system->phase->name}}</td>
	  <td><a href="{{route('systems.show', $system->id)}}">{{$system->displayName()}}</a></td>
	  @if ($system->inhabited())
	  <td>{{number_format($system->population, 0)}}</td>
	  <td>{{number_format($system->economySize(), 0)}}
      <td>
		@include($system->economy->icon)
		{{$system->economy->name}}
	  </td>
      <td>
		@include($system->controllingFaction()->government->icon)
		{{$system->controllingFaction()->government->name}}
	  </td>
	  <td>
		@if ($system->controllingFaction()->currentState($system))
		@include ($system->controllingFaction()->currentState($system)->icon)
		@endif
		<a href="{{route('factions.show', $system->controllingFaction()->id)}}">{{$system->controllingFaction()->name}}</a>
	  </td>
	  @else
	  <td>0</td>
	  <td></td>
	  <td></td>
	  <td></td>
	  <td></td>
	  @endif
	  <td>{{number_format($system->explorationvalue)}}</td>
	  <td data-search='
		@foreach ($system->facilities as $facility)
{{$facility->name}}
		@endforeach
		@if ($system->cfthmc > 0) Terraforming Candidate @endif
'>
		@foreach ($system->facilities->sortBy('name') as $facility)
		@if (!$facility->pivot->enabled)<span class='facility-disabled'>@endif
		  @include ($facility->icon)
		  @if (!$facility->pivot->enabled)</span>@endif
  		@endforeach
		@if ($system->cfthmc > 0)
		@include("icons/facilities/systems/terraformable")
		@endif
	  </td>
	  <td>{{$system->installations_count}}</td>
	  <td>{{$system->megashiproutes_count}}</td>
	  <td>{{$system->sites_count}}</td>
	</tr>
	@endforeach
  </tbody>
</table>

@endsection
