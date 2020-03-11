<p>Now, the {{$parameters['commodity']->category}} market update.</p>

<p>In {{$parameters['commodity']->description}} trading today the buyers are offering between {{number_format($parameters['minbuy'])}} and {{number_format($parameters['maxbuy'])}} credits, and cargo can be picked up for between {{number_format($parameters['minsell'])}} and {{number_format($parameters['maxsell'])}} credits.</p>

@if ($parameters['maxsell'] > $parameters['minbuy'])
<p>There's a bit of overlap there, so if you're hauling {{$parameters['commodity']->description}} today, consider your routes carefully.</p>
@else
<p>This is an excellent choice for independent haulers, as there's basically no way to lose money on transport.</p>
@endif

<p>
  @if ($parameters['surplus'] > 0)
  The region produces a surplus of around {{number_format($parameters['surplus'])}} tonnes a day, much of which finds its way onto our bulk trade ships for sale to the Sol region.
  @else
  The region is not currently self-sufficient for {{$parameters['commodity']->description}}, with around {{number_format(-$parameters['surplus'])}} extra tonnes needed daily for full production.
  @endif
  On the current markets, {{number_format($parameters['supply'])}} tonnes are in stock, and {{number_format($parameters['demand'])}} tonnes are being requested.
</p>
  


