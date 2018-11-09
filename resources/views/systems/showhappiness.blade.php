@extends('layout/layout')

@section('title')
{{$system->displayName()}}
@if ($system->name)
({{$system->catalogue}})
@endif
 - Happiness
@endsection

@section('content')

<p><a href='{{route("systems.show", $system->id)}}'>System details</a></p>

@include('layout/chart')
    
<table class='table table-bordered datatable' data-order='[[0, "desc"]]' data-searching='false' data-page-length='25'>
  <thead>
	<tr>
	  <th>Date</th>
	  @foreach ($factions as $faction)
	  <th data-orderable='false'>
		<a href='{{route('factions.showhappiness', $faction->id)}}'>{{$faction->name}}</a>
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
		@include('icons/happiness',['happiness'=>$history[$date][$factionid][0], 'label'=>true])
		@else
		
		@endif
	  </td>
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
@endsection
