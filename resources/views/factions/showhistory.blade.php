@extends('layout/layout')

@section('title')
{{$faction->name}} - History
@endsection

@section('content')

<p><a href='{{route("factions.show", $faction->id)}}'>Faction details</a></p>

@include('layout/chart')

{!! Form::open(['route' => ['factions.showhistory', 'faction' =>$faction->id], 'method' => 'GET']) !!}

{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}

{!! Form::submit('Set date range') !!}
{!! Form::close() !!}
    
<table class='table table-bordered datatable' data-order='[[0, "desc"]]' data-searching='false' data-page-length='25'>
  <thead>
	<tr>
	  <th>Date</th>
	  @foreach ($systems as $system)
      <th data-orderable='false'>
		<a href='{{route('systems.showhistory', $system->id)}}'>{{$system->displayName()}}</a>
	  </th>
	  @endforeach
	</tr>
  </thead>
  <tbody>
	@foreach ($dates as $date => $dummy)
	<tr>
	  <td data-sort='{{$date}}'>{{\App\Util::displayDate(new \Carbon\Carbon($date))}}</td>
	  @foreach ($systems as $systemid => $system)
	  <td>
		@if(isset($history[$date][$systemid]))
        @if(isset($history[$date][$systemid][0]))
		{{$history[$date][$systemid][0]}}
		@include('components.stateiconsmap', ['statekeys' => $history[$date][$systemid][1], 'states' => $states ])
		@else
		<span title="Not collected">?</span>
		@endif
		@else
		
		@endif
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
@endsection
