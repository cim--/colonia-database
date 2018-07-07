<div class='form-field'>
{!! Form::label('megashipclass_id', "Class") !!}
{!! Form::select('megashipclass_id', $classes) !!}
</div>
<div class='form-field'>
{!! Form::label('serial', "Serial") !!}
{!! Form::text('serial') !!}
</div>
<div class='form-field'>
{!! Form::label('commissioned', "Commissioned") !!}
{!! Form::text('commissioned') !!}
</div>
<div class='form-field'>
{!! Form::label('decommissioned', "Decommissioned") !!}
{!! Form::text('decommissioned') !!}
</div>
<div class='form-field'>
{!! Form::label('cargodesc', "Cargo Description") !!}
{!! Form::textarea('cargodesc') !!}
</div>

@if (isset($megaship))
@include('megaships.routeform')
@endif
