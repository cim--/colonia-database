@if ($parameters['system']->economy->name == "Agricultural")
<p>Crops in the {{$parameters['system']->name}} have failed due to agricultural blight. {{$parameters['faction']->name}} is requesting supplies of Agronomic Treatment from the region's high-tech centres, to clean hydroponics bays for future use. Supplies are likely to be disrupted for some time while the cleanup operation continues.</p>
@else
<p>Food stockpiles in {{$parameters['system']->name}} have become contaminated with pathogens, making them unfit for consumption. Replacement food supplies and Agronomic Treatment are requested by {{$parameters['faction']->name}} to resolve the issue.</p>
@endif

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
