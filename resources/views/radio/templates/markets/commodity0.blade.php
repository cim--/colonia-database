<p>Now, the {{$parameters['commodity']->category}} market update. {{$parameters['commodity']->description}} prices today are between {{number_format($parameters['minsell'])}} and {{number_format($parameters['maxsell'])}} credits for export, with buyers paying between {{number_format($parameters['minbuy'])}} and {{number_format($parameters['maxbuy'])}} credits.
  @if ($parameters['maxsell'] > $parameters['minbuy'])
  While there's money to be made, there's no guarantee of a profit for the haulers here, especially not after their operating costs.
  @else
  There's good money to be made here for the haulers who can match up those offered prices.
  @endif
</p>

<p>Current open market stocks throughout the region are {{number_format($parameters['supply'])}} tonnes, with bids open for {{number_format($parameters['demand'])}} tonnes of purchases.
  @if ($parameters['surplus'] > 0)
  Accounting for production and consumption rates, we can generate a surplus of around {{number_format($parameters['surplus'])}} tonnes a day.
  @else
  Accounting for production and consumption rates, that leaves us around {{number_format(-$parameters['surplus'])}} tonnes a day short 
  @if ($parameters['commodity']->category == "Minerals")
  although independent mining operations do provide some extra.
  @else
  without imports from elsewhere.
  @endif
  @endif
</p>


