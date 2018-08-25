@if ($mtype->blueprints->count() > 0)
@include('components.blueprint', ['blueprint' => $mtype->blueprints->sortByDesc('level')->first()])
@endif
