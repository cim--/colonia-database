<table class='table table-bordered'>
<thead>
  <tr><th>Code</th><th>Label</th><th>Target</th></tr>
</thead>
<tbody>
@foreach ($project->objectives as $objective)
<tr>
  <td>{{ Form::text('code'.$objective->id, $objective->code) }} </td>
  <td>{{ Form::text('label'.$objective->id, $objective->label) }} </td>
  <td>{{ Form::text('target'.$objective->id, $objective->target) }} </td>
</tr>
@endforeach
<tr>
  <td>{{ Form::text('code0') }} </td>
  <td>{{ Form::text('label0') }} </td>
  <td>{{ Form::text('target0') }} </td>
</tr>
</tbody>
</table>
