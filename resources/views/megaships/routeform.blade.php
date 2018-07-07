<p>Sequences start at 0 and must be unbroken integers</p>
<table class='table table-bordered'>
<thead>
  <tr><th>Sequence</th><th>System</th><th>or Other</th></tr>
</thead>
<tbody>
@foreach ($megaship->megashiproutes->sortBy('sequence') as $route)
<tr>
  <td>{{ Form::text('sequence'.$route->id, $route->sequence) }} </td>
  <td>{{ Form::select('system'.$route->id, $systems, $route->system_id) }}</td>
  <td>{{ Form::text('systemdesc'.$route->id, $route->systemdesc) }} </td>
</tr>
@endforeach
<tr>
  <td>{{ Form::text('sequence0') }} </td>
  <td>{{ Form::select('system0', $systems, 0) }}</td>
  <td>{{ Form::text('systemdesc0') }} </td>
</tr>
</tbody>
</table>
