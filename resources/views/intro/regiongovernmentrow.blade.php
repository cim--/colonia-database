	<tr>
	  <th scope='row'>
        @include ($government->icon)
		{{$government->name}}
	  </th>
	  @include('intro/regionpercent', ['value'=>$government->factions()->count(), 'total' => $factioncount, 'native' => true])
	  @foreach ($regions as $region)
	  @include('intro/regionpercent', ['value'=>$region->governments()->where('governments.id', $government->id)->first()->pivot->frequency, 'total' => $region->factions, 'native' => false])
	  @endforeach
	</tr>
