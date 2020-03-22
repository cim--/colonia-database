<p>Now, the {{$parameters['commodity']->category}} market update, looking at {{$parameters['commodity']->name}}.</p>

<p>For sale at the moment on the open market are {{number_format($parameters['supply'])}} tonnes at prices between {{number_format($parameters['minsell'])}} and {{number_format($parameters['maxsell'])}} credits.</p>

<p>Purchase orders are open for {{number_format($parameters['demand'])}} tonnes at prices between {{number_format($parameters['minbuy'])}} and {{number_format($parameters['maxbuy'])}} credits.</p>

<p>That's a potential profit of {{$parameters['maxbuy']-$parameters['minsell']}} credits on the most lucrative trade routes today, which should attract some ships to it.
  @if ($parameters['maxsell'] > $parameters['minbuy'])
  Of course, picking the wrong route could lead to a significant loss.
  @else
  Even the unluckiest hauler should still make about {{$parameters['minbuy']-$parameters['maxsell']}} credits a tonne.
  @endif
</p>

<p>
@if ($parameters['surplus'] > 0)
Some of the {{number_format($parameters['surplus'])}} tonnes a day surplus that is produced in the region is carried by the {{$parameters['megaship']->displayName()}} to the Sol region.
@else
The region is {{number_format(-$parameters['surplus'])}} tonnes a day short of full self-sufficiency at the moment, so the {{$parameters['megaship']->displayName()}} is used to import additional supplies from the Sol region.
@endif
This ship, currently in {{$parameters['megaship']->currentLocationName()}}, has been an occasional target for pirates and independent pilots are encouraged to assist when required.</p>



