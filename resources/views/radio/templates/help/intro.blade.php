<p>{{$picker->pickFrom([
  "If you're tuned in on your way up to Colonia for the first time, there are some important differences from the Sol region to be aware of, for example, in",
  "We now continue our Introduction to Colonia series, with some information on",
  "If you're new to the region, next up we have a piece from our Introduction to Colonia series, on"
  ])}}</p>

@include($parameters['article'], $parameters)

<p>{{$picker->pickFrom([
  "We'll have more from the Introduction to Colonia series later, but for now, back to the news.",
  "More on this later, after the news.",
  "Stay tuned for more in the Introduction to Colonia series later."
  ])}}</p>
