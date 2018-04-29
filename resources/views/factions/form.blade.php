<div class='form-field'>
{!! Form::label('name', "Name") !!}
{!! Form::text('name') !!}
</div>
<div class='form-field'>
{!! Form::label('url', "URL") !!}
{!! Form::text('url') !!}
</div>
<div class='form-field'>
{!! Form::label('eddb', "EDDB") !!}
{!! Form::text('eddb') !!}
</div>
<div class='form-field'>
{!! Form::label('player', "Is player?") !!}
{!! Form::checkbox('player', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('virtual', "Is virtual?") !!}
{!! Form::checkbox('virtual', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('hidden', "Is hidden?") !!}
{!! Form::checkbox('hidden', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('government_id', "Government") !!}
{!! Form::select('government_id', $governments) !!}
</div>
<div class='form-field'>
  {!! Form::label('ethos_id', "Ethos") !!}
  {!! Form::select('ethos_id', $ethoses) !!}
</div>
<div class='form-field'>
  {!! Form::label('system_id', "Home System") !!}
  {!! Form::select('system_id', $systems) !!}
</div>

