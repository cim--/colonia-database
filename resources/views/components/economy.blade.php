@if (isset($economies[$economy]))
{{$economies[$economy]}} @include($iconmap[$economy]) {{$economy}}
@else
0 @include($iconmap[$economy]) {{$economy}}
@endif
