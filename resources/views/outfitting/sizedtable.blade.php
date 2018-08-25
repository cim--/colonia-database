<table class='table table-bordered'>
  <thead>
	<tr>
	  <th rowspan='2'>Module Type</th>
	  <th colspan='8'>Size</th>
	</tr>
	<tr>
	  @for ($i=1;8>=$i;$i++)
	  <th>{{$i}}</th>
	  @endfor
	</tr>
  </thead>
  <tbody>
	@foreach ($types as $mtype)
	<tr>
	  <td>
		<a href='{{route('outfitting.moduletype', $mtype->id)}}'>{{$mtype->description}}</a>
		@include('outfitting.blueprint')
	  </td>
	  @for ($i=1;8>=$i;$i++)
	  @if ($mtype->modules->where('size', $i)->count() > 0)
	  @include ('outfitting.classcell', ['best' => $mtype->modules->where('size', $i)->sortBy('type')->first()->type])
	  @else
	  <td>-</td>
	  @endif
	  @endfor
	</tr>
	@endforeach
  </tbody>
</table>
