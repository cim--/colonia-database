@extends('layout/layout')

@section('title', 'Update Progress')

@section('content')

<p>Updates for tick {{\App\Util::displayDate($target) }} / day {{\App\Util::displayDate($today) }}</p>

@if (\App\Util::nearTick())

<p class='alert alert-warning'><strong>Warning:</strong> The tick is
expected to have occurred very recently. Use caution when updating
influence and state information especially if there appears to be no
visible change.</p>

@endif
        
@if ($reader)
<p class='alert alert-info'>EDDN Reader is running</p>
@else
<p class='alert alert-danger'>EDDN Reader is offline. Please report this on Discord.</p>
@endif
    
<p>The easiest things to update are listed first. Numbers after each item indicate the days since the last update.</p>
  
<h2>Systems needing influence update ({{number_format($influencecomplete)}}%)</h2>
@if (count($influenceupdate) > 0)
@if($userrank > 0)
<p>The following systems do not have influence updates on todays tick. Please ensure before starting that the tick is complete. Collect influence data from the system map only for accuracy - this does not require you to be in the system.</p>
@endif
<p>You can update this data without needing to log in by entering the system while running an EDDN-connected application (e.g. EDDiscovery, ED Market Connector or EDDI). Systems where the influence has not changed since yesterday cannot be updated by this route until at least four hours have passed since the tick.</p>
<ul class='compact'>
  @foreach ($influenceupdate as $system)
  <li>
	@if($userrank > 0)
	<a href="{{route('systems.edit',$system->id)}}">{{$system->displayName()}}</a>
	@else
	<a href="{{route('systems.show',$system->id)}}">{{$system->displayName()}}</a>
	@endif
    @if ($target !== $today)
	@include('progressage', ['date' => \App\Util::age($system->influences()->max('date'))-1])
	@else
	@include('progressage', ['date' => \App\Util::age($system->influences()->max('date'))])
	@endif
  </li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif



<h2>Systems needing report updates ({{number_format($reportscomplete)}}%)</h2>
@if (count($reportsupdate) > 0)
<p>The following systems do not have report updates today. You will need to dock at a station in the system to view traffic, crime and bounty reports in the local Galnet. This does not need daily updates for everywhere!</p>
@if($userrank == 0)
<p>This data can only be updated by logging in and entering it manually, or by using the <code>!addreport</code> command.</p>
@endif
<ul class='compact'>
  @foreach ($reportsupdate as $system)
  <li>
	@if($userrank > 0)
	<a href="{{route('systems.editreport',$system->id)}}">{{$system->displayName()}}</a>
	@else
	<a href="{{route('systems.show',$system->id)}}">{{$system->displayName()}}</a>
	@endif
    @include('progressage', ['date' => \App\Util::age($system->systemreports()->where('estimated', false)->max('date'))])
  </li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif

<h2>Stations needing market updates ({{number_format($marketscomplete)}}%)</h2>
@if (count($marketsupdate) > 0)
<p>The following stations do not have market updates today. You will need to dock at the station to upload market data. This does not need daily updates for everywhere!</p>
<p>Stations currently in Lockdown cannot be updated.</p>
<p>To send an update without needing to open the market, outfitting or shipyard screens, you can for now use <a href="https://www.edsm.net/en_GB/settings/import/capi">EDSM's CAPI tool</a>.</p>
<ul class='compact'>
  @foreach ($marketsupdate as $station)
  <li>
	<a href="{{route('stations.show',$station->id)}}">{{$station->name}}</a>
	@include('progressage', ['date' => \App\Util::age($station->reserves()->where('current', true)->max('date'))])
	@if ($station->currentStateList()->where('name','Lockdown')->count() > 0)
    @include('icons/states/lockdown')
	@endif
  </li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif

<h2>Alerts</h2>
@if ($alerts->count() == 0)
<p><strong>No pending alerts!</strong></p>
@else
<ul>
  @foreach ($alerts as $alert)
  <li>{{\App\Util::displayDate($alert->created_at)}}: {{$alert->alert}}
	@if($userrank >= 2)
	{!! Form::open(['route' => ['acknowledge', $alert->id], 'method'=>'Delete']) !!}
	{!! Form::submit("X") !!}
	{!! Form::close() !!}
	@endif
  </li>
  @endforeach
</ul>
@endif

@endsection
