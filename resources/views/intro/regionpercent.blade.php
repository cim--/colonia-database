<td
   @if($native)
   class='nativel'
   @endif
   >
  {{number_format($value)}}
</td>
<td
   @if($native)
   class='nativer'
   @else
   class='otherr'
   @endif
   >
  {{number_format($value*100/$total, 1)}}%
</td>
