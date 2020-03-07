<p>In the {{$parameters['system']->displayName()}} system, authorities are reporting traffic of around {{App\Util::sigFig($parameters['report'],1)}} ships a day. This is
  @if ($parameters['report'] > $parameters['average'] * 2)
  higher than normal, so expect greater delays if you are visiting.
  @elseif ($parameters['average'] > $parameters['report'] * 3)
  lower than normal.
  @else
  typical for the system.
  @endif
</p>

@if (!$parameters['haslarge'] && $parameters['report'] > 50)
<p>Travellers should remember that docking facilities are limited in this system, and so queues may be present at peak times. Please follow all traffic control instructions.</p>
@endif

@if ($parameters['law'] > 1000000)
<p>There are reports of significant pirate activity in this system, with around {{App\Util::sigFig($parameters['law'],1)}} credits of bounties being applied daily by the local authorities. Please ensure that your ship is prepared for hostilities if visiting the rougher areas.</p>
@elseif (10000 > $parameters['law'])
<p>
  The system is reporting relatively low levels of criminal activity at the moment
  @if (10 > $parameters['report'])
  although this may be due to low traffic levels in general giving pirates few targets.
  @else
  though as always pilots should remain alert.
  @endif
</p>
@endif

<p>All pilots are reminded to follow speed limits and maintain safe separation while in traffic controlled zones.</p>
