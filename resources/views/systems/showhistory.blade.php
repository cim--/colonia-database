@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@if ($system->name)
({{$system->catalogue}})
@endif
 - History
@endsection

@section('content')

<p><a href='{{route("systems.show", $system->id)}}'>System details</a></p>

@include('layout/chart')

{!! Form::open(['route' => ['systems.showhistory', 'system' =>$system->id], 'method' => 'GET']) !!}

{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}

{!! Form::submit('Set date range') !!}
<a href='{{route('systems.showhistory', ['system' =>$system->id])}}'>(Show all data)</a>
{!! Form::close() !!}
    

<table class='table table-bordered datatable' data-order='[[0, "desc"]]' data-searching='false' data-page-length='25'>
  <thead>
	<tr>
	  <th>Date</th>
	  @foreach ($factions as $faction)
	  <th data-orderable='false'>
		<a href='{{route('factions.showhistory', $faction->id)}}'>{{$faction->name}}</a>
	  </th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($dates as $date => $dummy)
	<tr>
	  <td data-sort='{{$date}}'>{{\App\Util::displayDate(new \Carbon\Carbon($date))}}</td>
	  @foreach ($factions as $factionid => $faction)
	      <td>
		  @if(isset($history[$date][$factionid]))
		      {{$history[$date][$factionid][0]}}
		      @include('components.stateiconsmap', ['statekeys' => $history[$date][$factionid][1], 'states' => $states ])
		@else
		
		@endif
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
@endsection
