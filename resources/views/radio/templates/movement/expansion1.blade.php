<p>
  @if ($retreating)
  Elsewhere, successful
  @else
  Successful
  @endif
  operations and high levels of public support for {{$expanding->faction->name}} are giving them a significant boost, and they are rumoured to be in the final stages of negotiations for another partner organisation contract.</p>

<p>No-one is going on the record yet to say what system they'll be helping out with in future, to add to their existing {{$expanding->faction->systemCount()}}
  @if ($expanding->faction->systemCount() > 1)
  contracts,
  @else
  contract,
  @endif 
  but we're expecting to hear very soon.
</p>

<p>
  In preparation for the move, {{$expanding->faction->name}} are stockpiling supplies at {{$picker->pickFrom($expanding->faction->stations()->tradable()->orderBy('id')->get())->name}} - imports are way up, and exports are down.
</p>
