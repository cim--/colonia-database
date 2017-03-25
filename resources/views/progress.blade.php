@extends('layout/layout')

@section('title', 'Update Progress')

@section('content')

@if($userrank > 0)
    <p>Updates for tick {{\App\Util::displayDate($target) }} / day {{\App\Util::displayDate($today) }}</p>
<h2>Systems needing influence update</h2>
@if (count($influenceupdate) > 0)
<p>The following systems do not have influence updates on todays tick. Please ensure before starting that the tick is complete. Collect influence data from the system map only - this does not require you to be in the system.</p>
<ul class='compact'>
  @foreach ($influenceupdate as $system)
  <li><a href="{{route('systems.edit',$system->id)}}">{{$system->displayName()}}</a></li>
  @endforeach
</ul>
@else
<p><strong>All systems updated!</strong></p>
@endif



<h2>Systems needing report updates</h2>
@if (count($influenceupdate) > 0)
<p>The following systems do not have report updates today. You will need to dock at a station in the system to view traffic, crime and bounty reports in the local Galnet</p>
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
