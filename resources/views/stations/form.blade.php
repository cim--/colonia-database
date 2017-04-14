<div class='form-field'>
{!! Form::label('name', "Name") !!}
{!! Form::text('name') !!}
</div>
<div class='form-field'>
  {!! Form::label('system_id', "System") !!}
  {!! Form::select('system_id', $systems) !!}
</div>
<div class='form-field'>
{!! Form::label('planet', "Planet") !!}
{!! Form::text('planet') !!}
</div>
<div class='form-field'>
{!! Form::label('distance', "Distance") !!}
{!! Form::text('distance') !!}
</div>
<div class='form-field'>
  {!! Form::label('stationclass_id', "Type") !!}
  {!! Form::select('stationclass_id', $classes) !!}
</div>
<div class='form-field'>
  {!! Form::label('economy_id', "Economy") !!}
  {!! Form::select('economy_id', $economies) !!}
</div>
<div class='form-field'>
  {!! Form::label('faction_id', "Controlling Faction") !!}
  {!! Form::select('faction_id', $factions) !!}
</div>
<div class='form-field'>
{!! Form::label('primary', "Is primary?") !!}
{!! Form::checkbox('primary', 1) !!}
</div>
<div class='form-field'>
{!! Form::label('eddb', "EDDB") !!}
{!! Form::text('eddb') !!}
</div>

@foreach ($stationFacilities as $facility)
<div class='form-field'>
  <input type='checkbox' name='facility[{{$facility->id}}]' value='{{$facility->id}}'
  @if (isset($station) && $station->facilities->contains($facility))
  checked='checked'
  @endif
  id='facility{{$facility->id}}'>
  <label for='facility{{$facility->id}}'>
	@includeIf($facility->icon)
	{{$facility->name}}
  </label>
  <select name='state[{{$facility->id}}]'>
	<option value='1' selected='selected'>Enable</option>
	<option value='0'>Disable</option>
  </select>
</div>
@endforeach
