	<tr>
	  <th scope='row'>
		@include ($economy->icon)
		{{$economy->name}}
	  </th>
	  @include('intro/regionpercent', ['value'=>$economy->systems()->count(), 'total' => $systemcount, 'native' => true])
	  @foreach ($regions as $region)
	  @include('intro/regionpercent', ['value'=>$region->economies()->where('economies.id', $economy->id)->first()->pivot->frequency, 'total' => $region->systems, 'native' => false])
	  @endforeach
	</tr>
