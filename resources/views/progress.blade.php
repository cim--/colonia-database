@extends('layout/layout')

@section('title', 'Update Progress')

@section('content')

@if($userrank > 0)
<p>Updates for tick {{\App\Util::displayDate($target) }} / day {{\App\Util::displayDate($today) }}</p>

<p>The easiest things to update are listed first.</p>
  
<h2>Systems needing influence update</h2>
@if (count($influenceupdate) > 0)
<p>The following systems do not have influence updates on todays tick. Please ensure before starting that the tick is complete. Collect influence data from the system map only for accuracy - this does not require you to be in the system.</p>
<ul class='compact'>
  @foreach ($influenceupdate as $system)
  <li><a href="{{route('systems.edit',$system->id)}}">{{$system->displayName()}}</a></li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif

<h2>Factions needing pending state updates</h2>
@if (count($pendingupdate) > 0)
<p>The following factions do not have pending state updates today. You will need to enter the system to view the pending states in the right panel. For a comprehensive survey, it is best to start by doing the Core outposts - as these have many factions only present there - then go to hub systems such as Dubbuennel where many CEI factions are present. It is not absolutely necessary to update every faction every day as pending states usually change slowly.</p>
<ul class='compact'>
  @foreach ($pendingupdate as $faction)
  <li><a href="{{route('factions.edit',$faction->id)}}">{{$faction->name}}</a></li>
  @endforeach
</ul>
@else
<p><strong>All factions updated!</strong></p>
@endif



<h2>Systems needing report updates</h2>
@if (count($reportsupdate) > 0)
<p>The following systems do not have report updates today. You will need to dock at a station in the system to view traffic, crime and bounty reports in the local Galnet. This does not need daily updates for everywhere!</p>
<ul class='compact'>
  @foreach ($reportsupdate as $system)
  <li><a href="{{route('systems.editreport',$system->id)}}">{{$system->displayName()}}</a></li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif


@endif

@endsection
