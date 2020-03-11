@if ($parameters['expanding'])
@include('radio.templates.movement.expansion0', ['expanding' => $parameters['expanding']])
@endif

@if ($parameters['retreating'])
@include('radio.templates.movement.retreat0', ['retreating' => $parameters['retreating'], 'expanding' => $parameters['expanding']])
@endif

@if ($parameters['expanding'] === null && $parameters['retreating'] === null)
<p>There is an unusual level of stability in the partner organisation agreements at the moment, with everyone seeming content with the current situation. A Council spokesperson suggested that this might be due to new negotiating procedures introduced recently, though other sources have pointed to the arrival of a large shipment of {{$picker->pickFrom('Lavian brandy', 'Centauri mega gin', 'Eranin pearl whiskey', 'Fujin tea', 'Indi bourbon')}}.</p>
@endif
