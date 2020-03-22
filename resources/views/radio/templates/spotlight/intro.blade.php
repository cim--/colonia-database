<p>We now take a more detailed look at {{$parameters['name']}}, as part of our Systems of Colonia series. {{$parameters['name']}} has a population of {{number_format($parameters['population'])}} centred around the {{$parameters['station']}} facility, and is currently administered primarily by {{$parameters['faction']}}.</p>

@include($parameters['detail'])

<p>{{$picker->pickFrom([
  "Our Systems of Colonia series will continue after the news.",
  "For more Systems of Colonia later, stay tuned.",
  "If you think there's anything else about this system or its inhabitants we should mention, please call in and let us know.",
  "More in this series later, after the latest headlines.",
  ])}}</p>
