@if ($blueprint->level < 2)
<span class='blueprint blueprint-level1' title='Grade 1 blueprints available, researching Grade 2'>
  <strong>&#x2699;&#xFE0E;</strong><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span>

@elseif ($blueprint->level < 3)
<span class='blueprint blueprint-level2' title='Grade 2 blueprints available, researching Grade 3'>
  <strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span>

@elseif ($blueprint->level < 4)
<span class='blueprint blueprint-level3' title='Grade 3 blueprints available, researching Grade 4'>
  <strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><span>&#x26EE;&#xFE0E;</span><span>&#x26EE;&#xFE0E;</span>

@elseif ($blueprint->level < 5)
<span class='blueprint blueprint-level4' title='Grade 4 blueprints available, researching Grade 5'>
  <strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><span>&#x26EE;&#xFE0E;</span>

@elseif ($blueprint->level == 5)
<span class='blueprint blueprint-level5' title='Grade 5 blueprints available'>
  <strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong><strong>&#x2699;&#xFE0E;</strong>

@endif
  @if (isset($fractional) && $fractional)
  ({{ ($blueprint->level - floor($blueprint->level))*100 }}%)
  @endif

    @if ($blueprint->partial)
    <span class='blueprint-partial' title='Only partial blueprints available'>&#x2762;</span>
    @endif
</span>
