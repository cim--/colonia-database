@if (count($parameters['assets']) == 0)
<p>Fighting has broken out in {{$parameters['conflict']->system->name}} between {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}} after a dispute over which partner organisation should assist in the operation of the system.</p>
@else
<p>Armed conflicts in {{$parameters['conflict']->system->name}} continue as control of {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
  is disputed between {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}}.
</p>
@if (count($parameters['assets']) > 1)
<p>Both sides claim that both structures should be run by them, and with neither willing to back down, the fighting will continue.</p>
@else
<p>The 
  @if ($parameters['conflict']->asset1)
  {{$parameters['conflict']->faction1->name}}
  @else
  {{$parameters['conflict']->faction2->name}}
  @endif
  are hoping for a quick victory over the enemies to retain control.
</p>
@endif

@endif

<p>At the moment
  @if ($parameters['direction'] == 0)
  both sides seem evenly matched, with little progress made,
  @elseif ($parameters['direction'] > 0)
  {{$parameters['conflict']->faction1->name}} has won several key battles,
  @else
  {{$parameters['conflict']->faction2->name}} is occupying several strategic resupply depots,
  @endif
  and reinforcements are arriving from their supporters
</p>
