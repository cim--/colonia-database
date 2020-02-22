@if (count($parameters['assets']) == 0)
<p>In {{$parameters['conflict']->system->name}}, {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}} are on the brink of war after negotiations over future partnership operations broke down.</p>
@else
<p>Negotiations in {{$parameters['conflict']->system->name}} now look unlikely to avert a conflict over {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
  as {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}} are refusing to discuss the matter further with each other, and are gathering their fleets.
</p>
@if (count($parameters['assets']) > 1)
<p>Both sides wish to see control of the structures consolidated under their rule, and it seems that a peaceful resolution is no longer possible.</p>
@else
<p>The 
  @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  are defending their current control of the structure, and have condemned
  @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction2->name}}
  @else
  {{$parameters['conflict']->faction1->name}}
  @endif
  for their aggression. 
  @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction2->name}},
  @else
  {{$parameters['conflict']->faction1->name}},
  @endif
  meanwhile, point to clauses in earlier treaties giving them the right to manage the {{$parameters['conflict']->asset1->displayType()}} and accuse their opponents of illegal occupation.</p>
@endif
@endif

