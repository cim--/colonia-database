<p>A band of pirates is currently raiding the {{$parameters['system']->name}} system, and has taken many hostages. While {{$parameters['faction']->name}} are purchasing weapons to defend facilities and make rescues, many independent citizens are requesting gems to satisfy the pirates ransom demands.</p>

<p>Reinforcements are being gathered and expect to repel the raids soon.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
