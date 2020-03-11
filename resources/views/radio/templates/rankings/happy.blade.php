@include('radio.templates.rankings.intro')

<p>By satisfaction of local population:</p>

<ul>
  @foreach (["First", "Second", "Third", "Fourth", "Fifth"] as $idx => $rank)
  <li>{{$rank}}: {{$parameters['top'][$idx]->name}}, {{number_format(125 - (25*$parameters['top'][$idx]->influences->avg('happiness')))}} percent satisfied.</li>
  @endforeach
</ul>
						     
