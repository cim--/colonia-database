@if (count($parameters['assets']) == 0)
<p>Citizens in {{$parameters['conflict']->system->name}} are going to the polls today, with a referendum on which partner organisations should operate the system in the coming weeks. Most incumbents are expected to carry on, but a strong challenge has led to a dispute over the final place, with {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}}.</p>
@else
<p>Citizens in {{$parameters['conflict']->system->name}} are going to the polls today, as the Governorship of {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
  is up for election. The leading candidates are those of {{$parameters['conflict']->faction1->name}} and of {{$parameters['conflict']->faction2->name}}.
</p>
@if (count($parameters['assets']) > 1)
<p>Both candidates have experience governing one of the assets in question already, and make strong cases that the two should be governed together - of course, they disagree on who should do that.</p>
@else
<p>The 
  @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  candidate's incumbency is thought by some commentators to be a significant advantage here, although others point to their opponent's {{$picker->pickFrom(['radical','costed','sensible','liberal','conservative','latest'])}} policy proposals as being more in tune with the electorate.</p>
@endif

@endif

<p>Current opinion polls suggest that
  @if ($parameters['direction'] == 0)
  it's too close to call
  @elseif ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}} is preferred by the public
  @else
  {{$parameters['conflict']->faction2->name}} is likely to win
  @endif
  and both sides are looking for campaign volunteers.
</p>
