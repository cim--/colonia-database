@if (count($parameters['assets']) == 0)
<p>In {{$parameters['conflict']->system->name}}, a referendum has been called after negotiations over which partner organisations would operate the system broke down. While most partners have now been agreed, negotiators were unable to decide between {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}}, and this will now go to a public vote.</p>
@else
<p>{{$parameters['conflict']->system->name}} authorities have called an election for the post of Governor of {{$parameters['assets'][0]->displayName()}}
  @if (count($parameters['assets']) > 1)
  and {{$parameters['assets'][1]->displayName()}}
  @endif
  after demands from the public. {{$parameters['conflict']->faction1->name}} and {{$parameters['conflict']->faction2->name}} have already declared their candidates.
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
  candidate is the current incumbent, and will be hoping that their record is enough to see off the challenge.</p>
@endif
@endif
<p>Voting opens soon, and both major parties are bringing supporters into the system in anticipation.</p>
