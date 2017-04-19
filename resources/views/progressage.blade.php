@if ($date >= 7)
<strong>({{$date}})</strong>
@elseif ($date >= 3)
<em>({{$date}})</em>
@elseif ($date >= 1)
({{$date}})
@endif
