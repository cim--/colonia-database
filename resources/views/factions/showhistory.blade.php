@extends('layout/layout')

@section('title')
{{$faction->name}} - History
@endsection

@section('content')

<p><a href='{{route("factions.show", $faction->id)}}'>Faction details</a></p>

<table class='table table-bordered datatable' data-order='[[0, "desc"]]' data-searching='false' data-pageLength='25'>
  <thead>
	<tr>
	  <th>Date</th>
	  @foreach ($systems as $system)
    <th>{{$system->displayName()}}</th>
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
		@include($history[$date][$systemid][1]->icon)
		{{$history[$date][$systemid][0]}}
		@else
		
		@endif
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
@endsection
