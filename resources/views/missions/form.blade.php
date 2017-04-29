<div class='form-field'>
{!! Form::label('type', "Type") !!}
{!! Form::text('type') !!}
</div>
<div class='form-field'>
{!! Form::label('reputationMagnitude', "Reputation Effect") !!}
{!! Form::select('reputationMagnitude', $positivemagnitudes) !!}
</div>
<h3>Source Faction</h3>
<div class='form-field'>
{!! Form::label('sourceInfluenceMagnitude', "Influence Effect") !!}
{!! Form::select('sourceInfluenceMagnitude', $positivemagnitudes) !!}
</div>
<div class='form-field'>
{!! Form::label('sourceState', "State Affected") !!}
{!! Form::select('sourceState', $states) !!}
</div>
<div class='form-field'>
{!! Form::label('sourceStateMagnitude', "State Effect") !!}
{!! Form::select('sourceStateMagnitude', $magnitudes) !!}
</div>

<h3>Destination Faction</h3>
<div class='form-field'>
{!! Form::label('hasDestination', "Has Destination?") !!}
{!! Form::checkbox('hasDestination') !!}
</div>
<div class='form-field'>
{!! Form::label('destinationInfluenceMagnitude', "Influence Effect") !!}
{!! Form::select('destinationInfluenceMagnitude', $positivemagnitudes) !!}
</div>
<div class='form-field'>
{!! Form::label('destinationState', "State Affected") !!}
{!! Form::select('destinationState', $states) !!}
</div>
<div class='form-field'>
{!! Form::label('destinationStateMagnitude', "State Effect") !!}
{!! Form::select('destinationStateMagnitude', $magnitudes) !!}
</div>
