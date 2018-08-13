@if (!$baseline)
<td data-sort='0'></td>
@else
@include('components/intensitycell', ['level' => $stats->getLevel($baseline->intensity), 'labels' => ['', 'Very low', 'Low', 'Average', 'High', 'Very high'], 'exact' => $baseline->intensity])
@endif
