@if ($blueprint->level == 1)
<span class='blueprint blueprint-level1' title='Grade 1 blueprints available, researching Grade 2'>
  <strong>&#x2699;</strong><span>&#x2699;</span><span>&#x2699;</span>
</span>
@elseif ($blueprint->level == 2)
<span class='blueprint blueprint-level2' title='Grade 2 blueprints available, researching Grade 3'>
  <strong>&#x2699;</strong><strong>&#x2699;</strong><span>&#x2699;</span>
</span>
@elseif ($blueprint->level == 3)
<span class='blueprint blueprint-level3' title='Grade 3 blueprints available'>
  <strong>&#x2699;</strong><strong>&#x2699;</strong><strong>&#x2699;</strong>
</span>
@endif
