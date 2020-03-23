@extends('layout/layout')

@section('title', 'Colonia Radio')

@section('content')

<script type='text/javascript'>
  var RadioSequenceNumber = {{$sequence}};
</script>

<p>Press the play button to start automatic speech synthesis playback of radio segments - requires Javascript and a browser which supports speech synthesis. Playback quality will depend on your browser and operating system. You can alternatively read the radio segments yourself using the fast-forward button.</p>

<div><button> Enable Speech Synthesis</button>
  (In some browsers you must press this button before pressing play)</div>

<p>Note: due to the way some browsers interact with speech synthesis, the pause button may only stop the radio at the end of the current segment.</p>
<div id='radiocontrols'>
  <button id='pausebutton'>&#x23f8;</button>
  <button id='playbutton'>&#x23f4;</button>
  <button id='nextbutton'><a href='{{route('radio.sequence', $sequence+1)}}'>&#x23ed;</a></button>
</div>
  
<div id='speechbox'>
  @include($template, ['parameters' => $parameters])
</div>




@endsection
