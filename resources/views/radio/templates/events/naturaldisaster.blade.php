<p>Settlements in {{$parameters['system']->name}} have been hit with {{$picker->pickFrom(['earthquakes', 'solar flares', 'meteor impacts', 'volcanic eruptions', 'flooding', 'hurricanes']) with many citizens injured, and property damage estimated at over {{$picker->pick(800)}} billion credits. The authorities are continuing to evacuate the area and make it safe.</p>

<p>While the majority of the damage has occurred in habitats under the management of {{$parameters['faction']->name}}, these provide much of the system's fresh water and power. {{$parameters['system']->name}}'s other partner organisations are said to be concerned that major shortages are imminent, and have collectively requested emergency aid from the Council.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
