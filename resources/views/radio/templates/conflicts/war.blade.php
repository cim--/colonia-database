<p>A ceasefire has been declared in {{$parameters['conflict']->system->name}} 
  @if ($parameters['direction'] == 0)
  after several days of inconclusive fighting brought both sides back to the negotiating table.
  @else
  after
  @if (0 > $parameters['direction'])
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  surrended.
  @endif
</p>

@if (count($parameters['assets']) == 0)
<p>
  @if ($parameters['direction'] == 0)
  We don't yet know which organisation will be continuing as an operating partner for the system, and what compensation they might receive from their rival to settle peacefully.
  @else
  @if ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}} 
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  will continue as an operating partner for the system.
  @endif
</p>
@else
<p>
  @if ($parameters['direction'] == 0)
  Early drafts of the peace treaty see the defenders retaining control of their structures.
  @else
  @if ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}} 
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  is now in undisputed control of
  {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
  @endif
</p>
@endif
