@if ($moduletype->blueprints->count() > 0)
<p>Engineering is available from:
  @foreach ($moduletype->blueprints as $blueprint)
  @if (!$loop->first),@endif
  <a href='{{route('engineers.show', $blueprint->engineer->id)}}'>{{$blueprint->engineer->name}}</a> (Grade {{$blueprint->level}})
  @endforeach
</p>
@endif
