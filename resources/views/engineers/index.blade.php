@extends('layout/layout')

@section('title')
Engineers
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('engineers.create')}}'>New engineer</a></p>
@endif

    
<table class='table table-bordered datatable' data-page-length='25' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>System</th>
      <th>Planet</th>
      <th>Station</th>
	  <th>Blueprints</th>
	  <th>Completed</th>
      <th>Development Level</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($engineers as $engineer)
	<tr>
	  <td>
		<a href='{{route('engineers.show', $engineer->id)}}'>
		  {{$engineer->name}}
		</a>
	  </td>
	  <td data-sort='{{$engineer->station->system->displayName()}}'>
		@include($engineer->station->system->economy->icon)
		<a href='{{route('systems.show', $engineer->station->system_id)}}'>
		  {{$engineer->station->system->displayName()}}
		</a>
	  </td>
	  <td>
		{{$engineer->station->planet}}
	  </td>
	  <td data-sort='{{$engineer->station->displayName()}}'>
		<a href='{{route('stations.show', $engineer->station_id)}}'>
		  {{$engineer->station->displayName()}}
		</a>
	  </td>
	  <td>
		{{$engineer->blueprints()->unduplicated()->count()}}
	  </td>
	  <td>
	    {{$engineer->blueprints()->unduplicated()->whereColumn('level', 'maxlevel')->count()}}
	  </td>
	  <td>
		{{number_format($engineer->blueprints()->unduplicated()->avg('level'),1)}}
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>

<h2>Engineering Research</h2>

<div class="engbox">
<div class="engbar" title="{{number_format($progress*100/$total)}}% complete" style="width:{{$progress*100/$total}}%">Estimated progress: {{number_format($progress)}}/{{number_format($total)}}</div>
</div>

<p>Colonia's engineers are researching higher grades of blueprints. Higher grades become available after approximately the following numbers of (cumulative) upgrades have been carried out on a particular module type.</p>

@if ($progress < $total)
<p>The Census offers a <a href='{{route('projects.index')}}'>project management service</a> for people wishing to undertake coordinated work to upgrade blueprints - contact Ian Doncaster if the blueprint you are interested in is not yet registered.</p>
<table class='table table-bordered'>
  <thead>
    <tr><td rowspan='2' colspan='2'></td><th scope="col" colspan='4'>Blueprints available</th></tr>
    <tr><th scope="col">Grade 2</th><th scope="col">Grade 3</th><th scope="col">Grade 4</th><th scope="col">Grade 5</th></tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan='6'>As the higher grade blueprints will be researched through a combination of rolls, the total required at any individual grade will be lower than stated here - the figures are if the entire research was made up of that grade once it becomes available.</td>
    </tr>
  <tbody>
    <tr><th scope="row" rowspan='4'>Upgrades made</th>
      <th scope="row">Grade 1</th><td>3000</td><td>7500</td><td>19000</td><td>48000</td></tr>
    <tr>
      <th scope="row">Grade 2</th><td>-</td><td>2250</td><td>8000</td><td>22500</td>
    </tr>
    <tr>
      <th scope="row">Grade 3</th><td>-</td><td>-</td><td>3800</td><td>13500</td>
    </tr>
    <tr>
      <th scope="row">Grade 4</th><td>-</td><td>-</td><td>-</td><td>7250</td>
    </tr>
  </tbody>
</table>

<p>It is believed that use of pinned blueprints and application of experimental effects do not contribute to research. You will need to log out and in to see an upgrade once the threshold is crossed.</p>
@endif

<p>Note that it is possible for an Engineer to have a higher research level than there exist blueprints - Mel Brandon has G5 Shield Cell Bank knowledge, but the SCB blueprints only exist up to G4. Similarly, Petra Olmanova cannot produce G2 High Capacity Chaff, Heatsinks or Point Defence (and neither, of course, can Ram Tah), despite having sufficient research level.</p>

@endsection
