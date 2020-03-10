@include('radio.templates.rankings.intro')

<p>By number of registered supporters:</p>

<ul>
  @foreach (["First", "Second", "Third", "Fourth", "Fifth"] as $idx => $rank)
  <li>{{$rank}}: {{$parameters['top'][$idx]->name}}, {{
          number_format(App\Util::sigFig(
              $parameters['top'][$idx]->influences->sum(function($i) {
                  return $i->influence * $i->system->population / 100;
              })
          ))
      }} supporters.</li>
  @endforeach
</ul>
						     
