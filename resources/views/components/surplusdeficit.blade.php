@if ($value)
@if ($value->reserves >= 0)
<span class='surplus'>{{$value->reserves}}</span>
@else
<span class='deficit'>{{$value->reserves}}</span>
@endif
@endif
