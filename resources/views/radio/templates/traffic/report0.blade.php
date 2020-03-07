<p>Our latest traffic report from the {{$parameters['system']->displayName()}} system estimates around {{App\Util::sigFig($parameters['report'],1)}} ships have passed through the system in the last day.</p>

@if ($parameters['report'] > $parameters['average'] * 2)
<p>This is much busier than normal, so be prepared for additional delays.</p>
@elseif ($parameters['average'] > $parameters['report'] * 3)
<p>It's an unusually quiet day, so probably a good time to visit if you don't want to spend too long queueing.</p>
@endif

@if ($parameters['report'] > 600)
<p>There are widespread reports of congestion, especially around major stations. Please follow all traffic control instructions when in the controlled zone, and maintain speed limits and safe separation for the safety of yourself and those around you.</p>
@elseif ($parameters['report'] > 100)
@if ($parameters['haslarge'])
<p>Travellers should use caution when entering and leaving stations, as the controlled zones are likely to be busy.</p>
@else
<p>There may be queues for landing pads at peak times, and your patience will be appreciated by traffic control.</p>
@endif
@elseif ($parameters['report'] > 10)
@if ($parameters['haslarge'])
<p>Traffic is generally moving smoothly at the landing pads and through the controlled zones.</p>
@else
<p>Traffic is generally moving smoothly but capacity is limited so please try to complete your business quickly to keep it that way.</p>
@endif
@else
<p>It's all very quiet, and queues are unlikely.</p>
@endif
