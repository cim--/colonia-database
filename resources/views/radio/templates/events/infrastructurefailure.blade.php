<p>{{$parameters['system']->name}} is currently experiencing major infrastructure failures after {{$picker->pickFrom(['what is believed to be deliberate sabotage', 'an unexpected cascade of shutdowns', 'a shortfall in scheduled maintenance'])}}. All industrial production has been halted as the settlements switch to emergency power, and {{$parameters['faction']->name}} has put out a general call for critical supplies while repairs are carried out.</p>

<p>Nearby systems are preparing shipments of basic foods, replacement components, and other key goods, and are calling for independent haulers to help with the urgent delivery.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
