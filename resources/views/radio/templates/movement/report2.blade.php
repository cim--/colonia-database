@if ($parameters['history'])

@if ($parameters['history']->description == "retreated from")
<p>Big news from {{$parameters['history']->location->displayName()}} as {{$parameters['history']->faction->name}} loses its right to operate as a partner organisation there. Officials in their headquarters in {{$parameters['history']->faction->system->name}} are said to be very disappointed with the news, but it's hard to find anyone in {{$parameters['history']->location->displayName()}} who agrees with them.</p>

@elseif ($parameters['history']->description == "expanded to")

<p>{{$parameters['history']->faction->name}} has just joined the other partner organisations of {{$parameters['history']->location->displayName()}} after negotiations concluded on {{$parameters['history']->date->format("F jS")}}. They're currently taking a minor role in operations, with responsibility for {{$picker->pickFrom(["land valuation", "mining disputes", "waste processing", "solar panel maintenance", "entertainments licenses", "traffic control", "refugee facilities", "carpentry and allied trades", "cargo stacking"])}}, but if this is successful and popular with the locals there are lots of additional opportunities for them.</p>

@elseif ($parameters['history']->description == "expanded by invasion to")

<p>There's a {{$picker->pickFrom(['dramatic','major','complex','local','savage'])}} dispute underway in {{$parameters['history']->location->displayName()}} as {{$parameters['history']->faction->name}} go public with their wish to be a partner organisation for the system.</p>

<p>The system council doesn't feel there's enough work to justify an eight-way split of responsibilities, but {{$parameters['history']->faction->name}} have put forward a strong case. If they want to succeed, though, they'll need to make a case that one of their rivals has to go - and it's not going to be pleasant.</p>

@endif

@else

@if ($parameters['retreating'])

@include('radio.templates.movement.retreat'.$picker->pick(2), ['retreating' => $parameters['retreating'], 'expanding' => null])

@elseif ($parameters['expanding'])
@include('radio.templates.movement.expansion'.$picker->pick(2), ['retreating' => null, 'expanding' => $parameters['expanding']])

@else
<p>There is an unusual level of stability in the partner organisation agreements at the moment, with everyone seeming content with the current situation. A Council spokesperson suggested that this might be due to new negotiating procedures introduced recently, though other sources have pointed to the arrival of a large shipment of {{$picker->pickFrom('Lavian brandy', 'Centauri mega gin', 'Eranin pearl whiskey', 'Fujin tea', 'Indi bourbon')}}.</p>
@endif

@endif
