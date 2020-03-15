<p>A major conference is underway to discuss changes to partner organisation management contracts, as the effectiveness of the current structure is questioned.</p>

@if ($parameters['expanding'] && $parameters['retreating'])
<p>Nothing is finalised yet, but delegates are understood to be considering two changes particularly strongly.</p>
@elseif (!$parameters['expanding'] && !$parameters['retreating'])
<p>Reports from the conferences so far are that very few proposals are getting a positive reception, and it may be that the end result is to leave contracts as they are for a little longer.</p>
@else
<p>One proposed change in particular appears to be attracting particular attention.</p>
@endif

@if ($parameters['retreating'])
<p>{{$parameters['retreating']->faction->name}} is facing tough questions about its operations in {{$parameters['retreating']->system->displayName()}} following {{$picker->pickFrom(["accusations of fraud", "failure to submit reports", "the arrest of senior officers on bribery charges", "serious delays to scheduled transports", "the shock relegation of the system's zero-G hockey team", "overheard comments at a private dinner", "the disappearance of artwork from the local museum"])}} and the general consensus seems to be that they should have their contract removed.</p>

<p>The leadership of {{$parameters['retreating']->faction->name}} has denied responsibility for the crisis, preferring to blame {{$picker->pickFrom(["its local rivals", "an umbrella mollusc migration", "a shortage of magnetic boots", "insufficient budgets", "the wrong sort of solar flares", "Federal spies"])}} for the situation. So far, this approach has not been convincing, but it's not quite over yet.</p>
@endif

@if ($parameters['expanding'])
<p>
  @if ($parameters['retreating'])
  Elsewhere,
  @endif
  {{$parameters['expanding']->faction->name}} is requesting an additional contract, to add to the {{$parameters['expanding']->faction->systemCount()}} they already hold, and pointing to their successful and popular management of their existing portfolio.</p>

<p>Delegates have already approved this in principle, and are now in discussions over which system the contract should be granted for. {{$picker->pickFrom($parameters['expanding']->faction->stations()->tradable()->orderBy('id')->get())->name}} is collecting construction materials and other supplies for the new facilities, and local shortages are reported.</p>
@endif
