@extends('layout/layout')

@section('title', 'Colonia Radio')

@section('content')

<script type='text/javascript'>
  var RadioSequenceNumber = {{$sequence}};
</script>

<p>Note: due to a bug in some browsers, the pause button may only stop the radio at the end of the current segment.</p>
<div id='radiocontrols'>
  <span id='pausebutton'>&#x23f8;</span>
  <span id='playbutton'>&#x23f4;</span>
  <span id='nextbutton'><a href='{{route('radio.sequence', $sequence+1)}}'>&#x23ed;</a></span>
</div>
  
<div id='speechbox'>
  @include($template, ['parameters' => $parameters])
</div>




@endsection
