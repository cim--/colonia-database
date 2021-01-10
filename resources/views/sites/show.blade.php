@extends('layout/layout')

@section('title')
@if ($site->sitecategory)
{{$site->sitecategory->name}}: {{$site->summary}}
@else
{{$site->summary}}
@endif
@endsection

@section('content')
@include('components/trackbox', ['domain' => 'sites', 'id' => $site->id])
@if ($userrank > 1)
<a class='edit' href='{{route('sites.edit', $site->id)}}'>Update</a>
@endif

<div><strong>Location:</strong>
  <a href='{{route('systems.show', $site->system_id)}}'>
	{{$site->system->displayName()}}
  </a> {{$site->planet}}
  @if ($site->coordinates)
  {{$site->coordinates}}
  @else
  in orbit
  @endif
</div>

<div>
  <strong>Type</strong>: {{$site->sitecategory->name}}.
  @if ($site->sitecategory->name == "Tip-off")
  Tip-off sites are primarily visited after a tip-off following a completed mission (though can be visited without this). They all contain some data source which can be read, and either crashed ships or a destroyed building. Tip-off sites are not always available: <a href="https://docs.google.com/spreadsheets/d/1xCGgDSJvwiufc1DHSnchko_jpEdpXH3KUlZT5FBSFBA/edit#gid=2038072426">Dja's spreadsheet</a> describes the schedule.
  @elseif ($site->sitecategory->name == "Listening Post")
  Orbital communications facilities which may either intentionally or through subversion provide messages.
  @elseif ($site->sitecategory->name == "Tourist Beacon")
  Beacons for passengers and other visitors marking sites of scientific, historical or cultural interest.
  @elseif ($site->sitecategory->name == "Attacked Ship")
  The wreckage of an attacked ship or other space construct
  @elseif ($site->sitecategory->name == "Alien")
  Xenobiological life forms or other xenological artifacts outside of lagrange clouds
  @elseif ($site->sitecategory->name == "Named Body")
  A planet or other body which has a name instead of a standard catalogue designation.
  @elseif ($site->sitecategory->name == "Lagrange Cloud")
  A Lagrange Cloud containing some forms of xenobiological life. As these are commonplace throughout the region, only a sample of those in inhabited systems are catalogued.
  @endif
</div>

<div>
<strong>Description:</strong> {!! $site->description !!}
</div>

@endsection
