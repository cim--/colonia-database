@include('radio.templates.rankings.intro')

<p>By number of system security contracts held:</p>

<ul>
  @foreach (["First", "Second", "Third", "Fourth", "Fifth"] as $idx => $rank)
  <li>{{$rank}}: {{$parameters['top'][$idx]->name}}, {{$parameters['top'][$idx]->stations->where('primary', 1)->count()}} contracts.</li>
  @endforeach
</ul>
						     
