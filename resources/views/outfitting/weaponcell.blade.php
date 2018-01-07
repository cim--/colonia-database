<td>
  @if ($weapon->modules->where('size', $size)->where('type', $mount)->count() == 0)
  -
  @else
  @if ($weapon->modules->where('size', $size)->where('type', $mount)->first()->stations_count > 0)
  @include('layout.yes')
  @else
  @include('layout.no')
  @endif
  @endif
</td>
