<p>Now, in fleet stock updates for {{$parameters['module']->moduletype->description}}.</p>

@if ($parameters['stations']->count() > 20)
<p>The {{$parameters['module']->displayName()}} is widely available at the region's shipyards.</p>
@elseif ($parameters['stations']->count() > 5)
<p>Stock of {{$parameters['module']->displayName()}} is available at many shipyards in the region.</p>
@else
<p>Stock of {{$parameters['module']->displayName()}} is limited and available at key shipyards only.</p>
@endif

<ul>
  @foreach ($parameters['dstations'] as $station)
  <li>{{$station->displayName()}}:
    @if ($station->pivot->current)
    @if ($station->pivot->unreliable)
    limited stock
    @else
    full stock
    @endif
    @else
    out of stock
    @endif
  </li>
  @endforeach
</ul>
