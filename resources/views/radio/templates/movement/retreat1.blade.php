<p>
  {{$retreating->faction->name}} is facing difficulties in
  {{$retreating->system->name}}, as complaints about the organisation
  are substantial, and its public support has collapsed.
</p>

<p>
  An emergency conference has been scheduled to discuss its continued
  future as a partner organisation for the system.
  @if ($retreating->influence > 2.5)
  {{$retreating->faction->name}} are taking this very seriously and have instituted reforms and an advertising campaign, in an attempt to keep the contract. At the moment, it looks like they might succeed.
  @else
  It's difficult to find anyone at the moment with a positive word for them, and if they don't turn things around soon, they'll be gone.
  @endif
</p>
