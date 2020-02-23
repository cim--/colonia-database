@if ($outcomes->pluck('name')->contains('Lockdown'))
<p>In response to the deteriorating security situation, authorities have imposed a full lockdown at all ports. Travellers are recommended to avoid the system except for essential business, and to leave extra time for security checks.</p>
@endif
@if ($outcomes->pluck('name')->contains('Civil Unrest'))
<p>The crisis is causing widespread unrest in settlements across the system, and criminals have been taking advantage of the situation to attack shipping. {{$parameters['faction']->name}} is offering large payouts to independent combat pilots able to assist in disrupting the pirate staging points.</p>
@endif
@if ($outcomes->pluck('name')->contains('Bust'))
<p>As the situation worsens, rolling shutdowns have been put in place at industrial facilities. Shelves are empty with purchases cut back to essentials only. The Council is discussing provision of emergency funding today - meanwhile, deliveries of Food Cartridges are requested to keep people fed while the economy is stabilised.</p>
@endif
@if ($outcomes->pluck('name')->contains('Famine'))
<p>Supplies of food in {{$parameters['system']->name}} are now completely exhausted, and urgent deliveries are required. Basic food cartridges, grain and vegetables are the highest priority for the system, but all foodstuffs will be gratefully received.</p>
@endif
