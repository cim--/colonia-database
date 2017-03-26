<td class='
	@if ($cell['present'])
	present-system
	@elseif ($cell['candidate'] && $cell['target'])
	target-system
	@elseif ($cell['candidate'] && !$cell['available'])
	future-system
	@elseif ($cell['candidate'] && $cell['full'])
	full-system
	@endif

	@if ($cell['distance'] == 0)
	same-system
    @elseif ($missions >= $cell['distance'])
    mission-system
	@elseif ($expansion >= $cell['distance'])
    expandable-system
    @endif
  '>
  {{number_format($cell['distance'], 2)}}
</td>
