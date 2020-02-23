<p>The {{$parameters['system']->name}} system is currently suffering from a major outbreak, with quarantine measures in place at all ports and facilities. {{$parameters['faction']->name}} is calling for the delivery of Medicines, Water Purifiers, and Pesticides, to help contain the disease.</p>

<p>{{10+$picker->pick(30)}} billion credits of aid has been granted by the Council for the purchase of emergency supplies, with commodity markets otherwise virtually shut down to prevent contamination.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
