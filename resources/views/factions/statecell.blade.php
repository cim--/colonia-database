<td data-search='
	@foreach ($states as $item)
	{{$item->name}}
  @endforeach
  '>
  @foreach ($states as $item)
  @include($item->icon)
  {{$item->name}}
  @endforeach
</td>
