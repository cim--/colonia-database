@include('radio.templates.rankings.intro')

<p>By number of partner organisation contracts held:</p>

<ul>
  @foreach (["First", "Second", "Third", "Fourth", "Fifth"] as $idx => $rank)
  <li>{{$rank}}: {{$parameters['top'][$idx]->name}}, {{$parameters['top'][$idx]->influences->count()}} contracts.</li>
  @endforeach
</ul>
						     
