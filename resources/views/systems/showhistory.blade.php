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

{!! str_replace(
    '"chart_xaxis_callback"',
    'chart_xaxis_callback',
    $chart->render()
) !!}
    
<table class='table table-bordered datatable' data-order='[[0, "desc"]]' data-searching='false' data-pageLength='25'>
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
		@include($history[$date][$factionid][1]->icon)
		{{$history[$date][$factionid][0]}}
		@else
		
		@endif
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
@endsection
