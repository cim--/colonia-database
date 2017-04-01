<div class='form-field'>
{!! Form::label('name', "Common Name") !!}
{!! Form::text('name') !!}
</div>
<div class='form-field'>
{!! Form::label('catalogue', "Catalogue Name") !!}
{!! Form::text('catalogue') !!}
</div>
<div class='form-field'>Coordinates (traditional)
{!! Form::label('x', "X") !!}
{!! Form::text('x') !!}
{!! Form::label('y', "Y") !!}
{!! Form::text('y') !!}
{!! Form::label('z', "Z") !!}
{!! Form::text('z') !!}
</div>
<div class='form-field'>
{!! Form::label('edsm', "EDSM") !!}
{!! Form::text('edsm') !!}
</div>
<div class='form-field'>
{!! Form::label('population', "Population") !!}
{!! Form::text('population', 0) !!}
</div>
<div class='form-field'>
{!! Form::label('phase_id', "Phase") !!}
{!! Form::select('phase_id', $phases) !!}
</div>
<div class='form-field'>
{!! Form::label('economy_id', "Economy") !!}
{!! Form::select('economy_id', $economies) !!}
</div>
