@extends('layout/layout')

@section('title', 'Visit tracking')

@section('content')

<p>There are a lot of points of interest in the Colonia region and it can be hard to keep track of whether you've visited all of them. This tool allows you to keep track of which you have visited.</p>

<p>If you turn the tracking tools on, a button to mark your visit will appear in the top right of the page for each point of interest in its respective catalogue. You can also click on the progress bars to mark visits here.</p>

<p>All information is stored locally on your web browser using Javascript - none of the information on what you have visited is ever sent to the census servers.</p>


<div id='tracktools'>
  <button id='enabletracktools'>Enable tracking tools</button>
  @include('visit/tracker', ['id' => 'systemtrack', 'label' => 'Systems', 'entries' => $systems])
  @include('visit/tracker', ['id' => 'stationtrack', 'label' => 'Stations', 'entries' => $stations])
  @include('visit/tracker', ['id' => 'factiontrack', 'label' => 'Factions', 'entries' => $factions])
  @include('visit/tracker', ['id' => 'installationtrack', 'label' => 'Installations', 'entries' => $installations])
  @include('visit/tracker', ['id' => 'megashiptrack', 'label' => 'Megaships', 'entries' => $megaships])
  @include('visit/tracker', ['id' => 'sitetrack', 'label' => 'Sites', 'entries' => $sites])
  @include('visit/tracker', ['id' => 'engineertrack', 'label' => 'Engineers', 'entries' => $engineers])

  <button id='disabletracktools'>Disable tracking tools</button>
</div>
@endsection
