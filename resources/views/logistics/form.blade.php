@extends('layout/layout')

@section('title')
Logistics Planner
@endsection

@section('content')

<div class='alert alert-danger'>
  Warning: due to recent economic changes there is insufficient data to consider faction states properly in this planner. Recommendations will be incomplete for now.
</div>

    
<p>The logistics planner allows you to set up bulk shipping operations and provides information which may be useful to optimise cargo transfer or estimate completion times.</p>

{!! Form::open(['route' => ['logistics.configure']]) !!}

<div class='form-field'>
  {!! Form::label('station_id', "Destination Station") !!}
  {!! Form::select('station_id', $stations) !!}
</div>
<div class='form-field'>
  {!! Form::label('volume', "Total Volume") !!}
  {!! Form::text('volume', 1200000) !!}
</div>
<div class='form-field'>
  {!! Form::label('duration', "Planned duration (days)") !!}
  {!! Form::text('duration', 7) !!}
</div>

<fieldset>
  <legend>Commodities</legend>
  <p>Only commodities with local production are listed. Plan other required commodities separately.</p>
  @for ($i=0;5>$i;$i++)
  <div class='form-field'>
	{!! Form::label('commodity'.$i, "Commodity") !!}
	{!! Form::select('commodity'.$i, $commodities) !!}
  </div>
  @endfor
</fieldset>

{!! Form::submit('Create logistics report') !!}

{!! Form::close() !!}

@endsection
