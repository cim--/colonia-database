@if ($effect)
@if ($effect->supplysize !== null)
<td>{{number_format($effect->supplysize, 2)}}</td>
<td>{{number_format($effect->supplyprice, 2)}}</td>
@else
<td></td>
<td></td>
@endif
@if ($effect->demandsize !== null)
<td>{{number_format($effect->demandsize, 2)}}</td>
<td>{{number_format($effect->demandprice, 2)}}</td>
@else
<td></td>
<td></td>
@endif
<td>
  @if ($effect->supplysize !== null)
  Supply: {{$effect->spass}}
  @endif
  @if ($effect->demandsize !== null)
  Demand: {{$effect->dpass}}
  @endif
</td>
@else
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
@endif
