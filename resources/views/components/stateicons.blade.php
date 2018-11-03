@if ($states)
@foreach ($states as $state)
@include ($state->icon)
@endforeach
@endif
