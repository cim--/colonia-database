<td @if ($data['sol']) class='sol' title='Known in Sol' @elseif ($data['count']) class='colonia' title='Unique to Colonia' @endif >
@if ($data['count'] == 0)
@include('layout/no')
@else
@include('layout/yes')
({{$data['count']}})
@endif
</td>
