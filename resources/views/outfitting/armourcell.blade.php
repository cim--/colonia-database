@if ($armours->where('description', $class)->first()->modules->where('type', $ship)->count() > 0)
@include('layout/yes')
@else
@include('layout/no')
@endif
