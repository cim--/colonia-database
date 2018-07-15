<div class='form-field'>
{!! Form::label('system_id', "System") !!}
{!! Form::select('system_id', $systems) !!}
</div>
<div class='form-field'>
{!! Form::label('planet', "Planet") !!}
{!! Form::text('planet') !!}
</div>
<div class='form-field'>
{!! Form::label('coordinates', "Coordinates") !!}
{!! Form::text('coordinates') !!}
</div>
<div class='form-field'>
{!! Form::label('summary', "Summary") !!}
{!! Form::text('summary') !!}
</div>
<div class='form-field'>
{!! Form::label('description', "Description") !!}
{!! Form::textarea('description') !!}
</div>

