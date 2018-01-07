<td>
  @if ($utility->modules->where('type', $class)->count() == 0)
  -
  @else
  @if ($utility->modules->where('type', $class)->first()->stations_count > 0)
  @include('layout.yes')
  @else
  @include('layout.no')
  @endif
  @endif
</td>
