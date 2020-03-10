@include('radio.templates.rankings.intro')

<p>By number of facilities governance contracts held:</p>

<ul>
  @foreach (["First", "Second", "Third", "Fourth", "Fifth"] as $idx => $rank)
  <li>{{$rank}}: {{$parameters['top'][$idx]->name}}, {{$parameters['top'][$idx]->stations->count() + $parameters['top'][$idx]->installations->count()}} contracts.</li>
  @endforeach
</ul>
						     
