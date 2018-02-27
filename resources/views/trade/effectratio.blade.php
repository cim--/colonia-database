@if($ratio->volumebalance !== null)
<div
   @if ($ratio->volumebalance >= 100)
  class="surplus"
  @elseif ($ratio->volumebalance <= 10)
  class="deficit"					 
									 @endif
   >{{number_format($ratio->volumebalance, 1)}}% T</div>
<div
   @if ($ratio->creditbalance >= 100)
  class="surplus"
  @elseif ($ratio->creditbalance <= 10)
  class="deficit"					 
									 @endif
   >{{number_format($ratio->creditbalance, 1)}}% Cr</div>
@endif
