<span class='happinesslevel'>
@if ($happiness == 1)
<span class='icon-happiness-elated' title="Elated">&#x1f642;&#xFE0E;&#x1f642;&#xFE0E;</span>
@if ($label) Elated @endif
@elseif ($happiness == 2)
<span class='icon-happiness-happy' title="Happy">&#x1f642;&#xFE0E;</span>
@if ($label) Happy @endif
@elseif ($happiness == 3)
<span class='icon-happiness-discontented' title="Discontented">&#x1f610;&#xFE0E;</span>
@if ($label) Discontented @endif
@elseif ($happiness == 4)
<span class='icon-happiness-unhappy' title="Unhappy">&#x1f641;&#xFE0E;</span>
@if ($label) Unhappy @endif
@elseif ($happiness == 5)
<span class='icon-happiness-despondent' title="Despondent">&#x1f641;&#xFE0E;&#x1f641;&#xFE0E;</span>
@if ($label) Despondent @endif
@else
-
@endif
</span>
