<p>Colonia's engineering research continues at {{$parameters['station']->displayName()}}, where {{$parameters['engineer']->name}} is working on better {{$parameters['blueprint']->moduletype->description}}s.</p>

<p>{{$parameters['engineer']->name}} 
  @if ($parameters['blueprint']->level - floor($parameters['blueprint']->level) > 0.75)
  says that a breakthrough has been made, and new improvements will be publicly available once final testing is completed and production facilities are scaled up. Additional materials to help build these facilities and make extra refinements to the process are still required.
  @elseif ($parameters['blueprint']->level - floor($parameters['blueprint']->level) > 0.5)
  believes that a breakthrough is very close. There is a high need for additional research materials to be brought to the facility.
  @elseif ($parameters['blueprint']->level - floor($parameters['blueprint']->level) > 0.25)
  claims early results are promising and with extra materials it is likely that a more advanced {{$parameters['blueprint']->moduletype->description}} can be constructed.
  @elseif ($parameters['blueprint']->level - floor($parameters['blueprint']->level) > 0.1)
  is investigating potential lines of enquiry for further improvements, and needs large amounts of research materials and data to support this.
  @else
  is currently celebrating after their recent breakthrough was brought to production, and thanks all pilots who brought in the research materials required. Theoretical research suggests further improvements are possible and a new programme will begin soon.
  @endif
</p>

@if ($parameters['blueprint']->level >= 4)
<p>Researchers are hopeful that their success on {{$parameters['finished']}}s will soon be repeated.</p>
@else
<p>While so far research on {{$parameters['blueprint']->moduletype->description}}s has not yet reached the same success as earlier work on {{$parameters['finished']}}s, progress so far has been strong with {{floor($parameters['blueprint']->level)}} milestones already met.</p>
@endif
