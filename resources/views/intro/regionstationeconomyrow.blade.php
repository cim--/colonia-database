	<tr>
	  <th scope='row'>
		@include ($economy->icon)
		{{$economy->name}}
	  </th>
	  @if ($economy->name == "Industrial")
	  @include('intro/regionpercent', ['value'=>3+$economy->stations->count(), 'total' => $stationcount, 'native' => true])
	  @else
	  @include('intro/regionpercent', ['value'=>$economy->stations->count(), 'total' => $stationcount, 'native' => true])
	  @endif
	  @foreach ($regions as $region)
	  @include('intro/regionpercent', ['value'=>$region->economies->where('id', $economy->id)->first()->pivot->stationfrequency, 'total' => $region->stations, 'native' => false])
	  @endforeach
	</tr>
