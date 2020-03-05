<p>
  Voting is underway in {{$parameters['conflict']->system->name}} as candidates from {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}} compete in the system's latest election.
</p>

@if (count($parameters['assets']) == 0)
<p>The winner will receive the last remaining seat on the partner organisation council for the system, while the loser will be expected to cease local operations.</p>
@else

@if (count($parameters['assets']) > 1)
<p>A proposal to merge the governance of {{$parameters['assets'][0]->displayName()}} and {{$parameters['assets'][1]->displayName()}} was recently approved, and the existing controllers of each facility are now competing for the post.</p>
@else

<p>At stake is the governance of {{$parameters['assets'][0]->displayName()}}, with @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  hoping that the electorate will reappoint them.
</p>

@endif
@endif

<p>Local commentators believe that the 
  @if ($parameters['direction'] == 0)
  election is likely to be extremely close and
  @elseif ($parameters['direction'] > 0)
  election will probably be won by {{$parameters['conflict']->faction1->name}} but
  @else
  electorate favours {{$parameters['conflict']->faction2->name}} but
  @endif
  neither side is going to concede yet.
</p>
