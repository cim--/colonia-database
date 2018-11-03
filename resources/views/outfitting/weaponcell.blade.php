<td>
  @if ($weapon->modules()->where('size', $size)->where('type', $mount)->count() == 0)
  -
  @else
  @if ($weapon->modules->where('size', $size)->where('type', $mount)->first())
  @include('layout.yes')
  @else
  @include('layout.no')
  @endif
  @endif
</td>
