	<tr>
	  <th scope='row'>
		@include ($economy->icon)
		{{$economy->name}}
	  </th>
	  @include('intro/regionpercent', ['value'=>$economy->stations->count(), 'total' => $factorycount, 'native' => true])
	  @foreach ($regions as $region)
	  @include('intro/regionpercent', ['value'=>$region->economies->where('id', $economy->id)->first()->pivot->factoryfrequency, 'total' => $region->factories, 'native' => false])
	  @endforeach
	</tr>
