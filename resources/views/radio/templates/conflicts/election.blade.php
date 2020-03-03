<p>Polls have closed in {{$parameters['conflict']->system->name}} and the votes are now being counted. Exit polls suggest that
  @if ($parameters['direction'] == 0)
  it is likely the incumbents will narrowly retain control
  @else
  victory has gone to
  @if ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  @endif
  on a turnout of {{30+$picker->pick(60)}}%.
</p>

@if (count($parameters['assets']) == 0)
<p>
  @if ($parameters['direction'] == 0)
  Both sides are waiting nervously for the full counts to conclude to determine which one will be chosen as an operating partner for the system.
  @else
  @if ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction2->name}} 
  @else
  {{$parameters['conflict']->faction1->name}}
  @endif
  has conceded defeat and is closing its offices in the system.
  @endif
</p>
@else
<p>
  @if ($parameters['direction'] == 0)
  It looks likely that the incumbents will remain in control of
  @else
  @if ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}} 
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  will now take on the Governorship of
  @endif
  {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
</p>
@endif
