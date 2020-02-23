<p>{{$picker->pickFrom(['Sabotage', 'An accident', 'An emergency', 'A breakdown'])}} at {{$parameters['system']->name}}'s primary water purification plant is being blamed for a major shortage of drinkable water in the system. As emergency supplies dwindle, {{$parameters['faction']->name}} is requesting both clean water and food, as well as replacement water purifiers.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
