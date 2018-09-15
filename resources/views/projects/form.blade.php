<div>
  {!! Form::label('code', 'Project Code') !!}
  {!! Form::text('code') !!}
</div>
<div>
  {!! Form::label('summary', 'Project Summary') !!}
  {!! Form::text('summary') !!}
</div>
<div>
  {!! Form::label('description', 'Project Description') !!}
  {!! Form::textarea('description', null, ['rows'=>'10','cols'=>80]) !!}
</div>
<div>
  {!! Form::label('url', 'Additional URL') !!}
  {!! Form::text('url') !!}
</div>
<div>
  {!! Form::label('complete', 'Completed?') !!}
  {!! Form::checkbox('complete', 1) !!}
</div>
<div>
  {!! Form::label('priority', 'Priority #') !!}
  {!! Form::text('priority') !!}
</div>

@if (isset($project))
@include('projects.objectiveform')
@endif
