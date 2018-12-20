<div class='form-field'>
{!! Form::label('installationclass_id', "Class") !!}
{!! Form::select('installationclass_id', $classes) !!}
</div>
<div class='form-field'>
{!! Form::label('system_id', "System") !!}
{!! Form::select('system_id', $systems) !!}
</div>
<div class='form-field'>
{!! Form::label('faction_id', "Faction") !!}
{!! Form::select('faction_id', $factions) !!}
</div>
<div class='form-field'>
{!! Form::label('planet', "Planet") !!}
{!! Form::text('planet') !!}
</div>
<div class='form-field'>
{!! Form::label('name', "Name") !!}
{!! Form::text('name') !!}
</div>
<div class='form-field'>
{!! Form::label('satellites', "Satellites?") !!}
{!! Form::checkbox('satellites', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('trespasszone', "Trespass?") !!}
{!! Form::checkbox('trespasszone', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('cargo', "Cargo Description") !!}
{!! Form::textarea('cargo') !!}
</div>
<div class='form-field'>
{!! Form::label('constructed', "Construction Date") !!}
{!! Form::text('constructed') !!}
</div>

