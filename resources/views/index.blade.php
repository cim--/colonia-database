@extends('layout/layout')

@section('title', 'Colonia Region System Database')

@section('content')

<div class='row'>
  <div class='col-sm-6'>
	<h2>Key Figures</h2>
	<ul>
      <li>Inhabited region is {{number_format($maxdist)}} LY in radius, with Colonia {{number_format($coldist)}} LY from the centre</li>
	  <li>{{$populated}} systems supporting {{number_format($population)}}
		@if ($unpopulated)
		people, with {{$unpopulated}} more currently planned
		@else
		people
		@endif</li>
	  <li>{{$dockables}} surface and orbital stations (and {{$stations->count()-$dockables}} settlements) including {{$engineers}} engineers</li>
      <li>Current happiness is {{number_format($happiness/$population)}}%:
	@foreach ($happinesses as $idx => $pop)
	@if ($pop > 0)
	@if ($idx > 1) - @endif
	{{number_format($pop)}} {{\App\Models\Influence::happinessAsString($idx)}}
	@endif
	@endforeach
      </li>
	  <li>Modern production facilities manufacture goods with {{number_format(100*$ecsize/$population)}}% of standard efficiency.</li>
	  <li>{{$instcount}} installations and {{$megacount}} operational mobile megaships</li>
	  <li>{{$factions->count()}} factions, including {{$players}} player factions</li>
	  <li>The busiest system saw {{$maxtraffic}} ships in 24 hours, the quietest only {{$mintraffic}}</li>
	  <li>Approximately {{number_format($bounties)}} million credits of bounties are collected daily in the region</li>
	  <li>The exploration value of the inhabited and planned systems is estimated at {{number_format($exploration)}} credits.</li>
	  <li>The currently inhabited systems have {{$terraformable}} terraforming candidates which do not have any native life.</li>
	  <li>Native life is being protected from invasion on {{$elwcount}} Earth-like Worlds, {{$wwcount}} terraformable Water Worlds and {{$awcount}} Ammonia Worlds.</li>
	</ul>

	<h2>Economies</h2>
	<ul id='economyflow'>
	  <li>
		@include('components/economy', ['economy' => 'Extraction'])
		<br>@include('components/economy', ['economy' => 'Refinery'])
	  </li>
	  <li>
		&#x27a0; @include('components/economy', ['economy' => 'Industrial'])
		<br>&#x27a0; @include('components/economy', ['economy' => 'High-Tech'])
	  </li>
	  <li>
		&#x27a0; @include('components/economy', ['economy' => 'Agricultural'])
		<br>&#x27a0; @include('components/economy', ['economy' => 'Service'])
		<br>&#x27a0; @include('components/economy', ['economy' => 'Military'])
	  </li>
	  <li>
		&#x27a0; @include('components/economy', ['economy' => 'Tourism'])
		<br>&#x27a0; @include('components/economy', ['economy' => 'Colony'])
		<br>&#x27a0; @include('components/economy', ['economy' => 'Prison'])
	  </li>
	</ul>
	  
	<h2>Governments</h2>
	<ul class='compact2'>
	  @foreach ($governments as $type => $count)
	  <li>
		{{$count}}
		@include ($iconmap[$type])
		{{$type}}
	  </li>
	  @endforeach
	</ul>

	{!! $ethoschart->render() !!}

	
	<h2>States</h2>

	<div id='statesummary'>
	  <div class='statebreakdown'>
		<h3>Factions</h3>
		<ul class='compact2'>
		  @foreach ($states as $state)
		  <li>
			{{$state['count']}}
			@include ($state['state']->icon)
			{{$state['state']->name}}
		  </li>
		  @endforeach
		</ul>
	  </div>
	  <div class='statebreakdown'>
		<h3>Systems</h3>
		<ul class='compact2'>
		  @foreach ($states as $state)
		  <li>
			{{$state['syscount']}}
			@include ($state['state']->icon)
			{{$state['state']->name}}
		  </li>
		  @endforeach
		</ul>
	  </div>
	</div>

	<h2>Find out more</h2>
	<ul>
	  <li><a href="{{route('stations.index')}}#cartographics">Where can I sell exploration data</a> or <a href="{{route('stations.index')}}#shipyard">buy a new ship?</a></li>
	  <li><a href="{{route('systems.index')}}#&quot;metallic ring&quot;">Where are the pristine metallic rings?</a></li>
	  <li><a href="{{route('factions.index')}}#Colonia">Which factions are named after Colonia?</a></li>
	  <li><a href="{{route('missions.index')}}#lockdown">Which missions affect Lockdown?</a></li>
      <li><a href="{{route('history')}}#founded">When were the systems founded?</a></li>
	  <li><a href="{{route('reports')}}?type=traffic">Which are the busiest systems?</a></li>
	  <li><a href="{{route('map')}}#XZ~F:Jaques~S:Colonia~P~1">Where are the best drinks?</a></li>
	  <li><a href="{{route('outfitting')}}">What equipment is available</a> and <a href="{{route('stations.index')}}#high-quality">where should I look for it?</a></li>
	  <li><a href="{{route('reserves')}}">Which commodities are available for trade?</a></li>
	  <li><a href="{{route('map')}}#XZ~C:control~C:control~X~1~0~0">Where have groups expanded</a> and <a href="{{route('reports.control')}}">which are the largest?</a></li>
	</ul>

	<script type='text/javascript'>
	  var wordmapdata = [
	  @foreach ($wordmap as $word => $count)
	  { text: "{!! $word !!}", size: {{$count}}, colour: "#{{\App\Util::wordColour($word, $count)}}" },
	  @endforeach
	  ]
	</script>
	<div id='wordcloud'></div>
	
  </div>

  <div class='col-sm-6'>
    @if ($projects->count() > 0)
    <h2>Current Projects</h2>
    <ul>
      @foreach ($projects as $project)
      <li><a href='{{route('projects.show', $project->id)}}'>{{$project->summary}}</a></li>
      @endforeach
    </ul>
    @endif

    <h2>Current Conflicts</h2>
    <table class='table table-bordered datatable' data-paging='false' data-order='[[1, "asc"],[2, "asc"]]' data-searching='false' data-info='false'>
      <thead>
	<tr>
	  <th>State</th><th>System</th><th>Faction 1</th><th>Score</th><th>Faction 2</th>
	</tr>
      </thead>
      <tbody>
	@if ($conflicts->count() > 0)
	@foreach ($conflicts as $conflict)
	<tr
	 @if (($conflict->asset1 && $conflict->asset1->isController()) || ($conflict->asset2 && $conflict->asset2->isController()))
	  class='controlconflict'
	  @endif
	  >
          <td>{{ucwords($conflict->status)}} {{ucwords($conflict->type)}}</td>
	  <td>
	    <a href='{{route('systems.show', $conflict->system->id)}}'>
	      {{$conflict->system->displayName()}}
	    </a>
	  </td>
	  <td>
	    @include($conflict->faction1->government->icon)
	    <a href='{{route('factions.show', $conflict->faction1->id)}}'>
	      {{$conflict->faction1->name}}
	    </a>
	    @if ($conflict->asset1)
	    (<a href='{{route($conflict->asset1->displayRoute(), $conflict->asset1->id)}}'>{{$conflict->asset1->displayName()}}</a>)
	    @endif
	  </td>
	  <td>{{$conflict->score}}</td>
	  <td>
	    @include($conflict->faction2->government->icon)
	    <a href='{{route('factions.show', $conflict->faction2->id)}}'>
	      {{$conflict->faction2->name}}
	    </a>
	    @if ($conflict->asset2)
	    (<a href='{{route($conflict->asset2->displayRoute(), $conflict->asset2->id)}}'>{{$conflict->asset2->displayName()}}</a>)
	    @endif
	  </td>
	</tr>
	@endforeach
	@else
	<tr><td colspan='5'>No current conflicts</td></tr>
	@endif
      </tbody>
    </table>
    
    
    <h2>Current Events</h2>
    <ul id='major-events'>
      @foreach ($importants as $important)
      @foreach ($important->states as $state)
      @if (!in_array($state->name, ['Boom', 'Civil Liberty', 'Investment', 'None', 'War', 'Election']))
      @if ($state->name != "Expansion" || $important->system_id == $important->faction->system_id)

      <li>
	@include($important->faction->government->icon)
	<a href='{{route('factions.show', $important->faction->id)}}'>
	  {{$important->faction->name}}
	</a>
	in
	@include($state->icon)
	{{$state->name}}
	@if ($state->name != "Expansion")
	in
	@include($important->system->economy->icon)
	<a href='{{route('systems.show', $important->system->id)}}'>
	  {{$important->system->displayName()}}
	</a>
	@endif
      </li>

      @endif
      @endif
      @endforeach
      @endforeach
      @foreach ($historys as $history)
      <li>
	@include($history->faction->government->icon)
	<a href='{{route('factions.show', $history->faction->id)}}'>
	  {{$history->faction->name}}
	</a>
	{{$history->description}}
	@if ($history->location_type == 'App\Models\System')
	@include($history->location->economy->icon)
	<a href='{{route('systems.show', $history->location->id)}}'>
	  {{$history->location->displayName()}}
	</a>
	@elseif ($history->location_type == 'App\Models\Station')
	@include($history->location->economy->icon)
	<a href='{{route('stations.show', $history->location->id)}}'>
	  {{$history->location->name}}
	</a>
	@endif 
	
      </li>
      @endforeach
      @foreach ($lowinfluences as $lowinfluence)
      <li>
	@include($lowinfluence->controllingFaction()->government->icon)
	<a href='{{route('factions.show', $lowinfluence->controllingFaction()->id)}}'>
	  {{$lowinfluence->controllingFaction()->name}}
	</a>
	<strong>low influence</strong> in
	@include($lowinfluence->economy->icon)
	<a href='{{route('systems.show', $lowinfluence->id)}}'>
	  {{$lowinfluence->displayName()}}
	</a>
      </li>
      @endforeach
      @foreach ($risks as $risk)
      <li>
	@include($risk->controllingFaction()->government->icon)
	<a href='{{route('factions.show', $risk->controllingFaction()->id)}}'>
	  {{$risk->controllingFaction()->name}}
	</a>
	<em>falling influence</em> in
	@include($risk->economy->icon)
	<a href='{{route('systems.show', $risk->id)}}'>
	  {{$risk->displayName()}}
	</a>
      </li>
      @endforeach
    </ul>


  </div>
</div>    

@endsection
