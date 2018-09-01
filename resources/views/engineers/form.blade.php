<div class='col-lg-6'>
<fieldset><legend>Location</legend>
  <div class='form-field'>
	{!! Form::label('station_id', "Station") !!}
	{!! Form::select('station_id', $stations) !!}
  </div>
  <div class='form-field'>
	{!! Form::label('faction_id', "Faction") !!}
	{!! Form::select('faction_id', $factions) !!}
  </div>
</fieldset>
<fieldset><legend>Description</legend>
  <div class='form-field'>
	{!! Form::label('name', "Name") !!}
	{!! Form::text('name') !!}
  </div>
  <div class='form-field'>
	{!! Form::label('discovery', "Discovery") !!}
	{!! Form::textarea('discovery') !!}
  </div>
  <div class='form-field'>
	{!! Form::label('invitation', "Invitation") !!}
	{!! Form::textarea('invitation') !!}
  </div>
  <div class='form-field'>
	{!! Form::label('access', "Access") !!}
	{!! Form::textarea('access') !!}
  </div>
</fieldset>
</div>
<div class='col-lg-6'>
<fieldset><legend>Blueprints</legend>
  @foreach ($moduletypes as $mtype)
  <div class='form-field'>
	{!! Form::label('blueprint'.$mtype->id, $mtype->type.": ".$mtype->description) !!}
	{!! Form::input('number', 'blueprint'.$mtype->id, $blueprints->where('moduletype_id', $mtype->id)->first() ? $blueprints->where('moduletype_id', $mtype->id)->first()->level : 0, ['min'=>0, 'max'=>5, 'step'=>1]) !!}
  </div>
  @endforeach
</fieldset>
</div>
