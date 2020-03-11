<p>{{$picker->pick(4)+1}} more bombs have exploded in {{$parameters['system']->name}} today as the terrorist attacks continue, causing the loss of {{$picker->pick(10)}} thousand tonnes of supplies, though fortunately only {{$picker->pick(4)+1}} people received minor injuries. {{$parameters['faction']->name}} authorities insist that they are close to tracking the attackers down, but refuse to comment further on operational matters.</p>

<p>Markets in the system are reporting increased demands for goods to try to replace the lost stock.</p>

@include('radio.templates.events.outcomes', ['outcomes' => $parameters['outcomes']])
