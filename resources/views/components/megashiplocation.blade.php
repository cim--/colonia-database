@if (is_object($location))
<a href="{{route('systems.show', $location->id)}}">
  {{$location->displayName()}}
</a>
@else
{{$location}}
@endif
  
