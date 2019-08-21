<span class='blueprint blueprint-level{{floor($blueprint->level)}}'
     @if ($blueprint->level < $blueprint->maxlevel)
     title='Grade {{floor($blueprint->level)}} blueprints available, researching Grade {{floor($blueprint->level)+1}}'
     @else
     title='Grade {{floor($blueprint->level)}} blueprints available, research complete'
     @endif
    >
    {{-- only way to deal with excess whitespace --}}
    @php
    for ($i=1;$i<=floor($blueprint->level);$i++) {
      echo "<strong>&#x2699;&#xFE0E;</strong>";
    }
      for ($i=floor($blueprint->level)+1;$i<=$blueprint->maxlevel;$i++) {
	echo "<span>&#x2699;&#xFE0E;</span>";
	}
	@endphp
	
    @if (isset($fractional) && $fractional && $blueprint->maxlevel > $blueprint->level)
    ({{ ($blueprint->level - floor($blueprint->level))*100 }}%)
    @endif

    @if ($blueprint->partial)
    <span class='blueprint-partial' title='Only partial blueprints available'>&#x2762;</span>
    @endif
</span>
