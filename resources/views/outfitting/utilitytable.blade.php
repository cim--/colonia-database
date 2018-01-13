<table class='table table-bordered'>
  <thead>
	<tr>
	  <th rowspan='2'>Utility</th>
	  <th colspan='5'>Class</th>
	</tr>
	<tr>
	  <th>A</th>
	  <th>B</th>
	  <th>C</th>
	  <th>D</th>
	  <th>E</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($utilities as $utility)
	<tr>
      <td>
		<a href='{{route('outfitting.moduletype', $utility->id)}}'>{{$utility->description}}</a>
	  </td>
	  @include('outfitting.utilitycell', ['class' => 'A', 'utility' => $utility])
	  @include('outfitting.utilitycell', ['class' => 'B', 'utility' => $utility])
	  @include('outfitting.utilitycell', ['class' => 'C', 'utility' => $utility])
	  @include('outfitting.utilitycell', ['class' => 'D', 'utility' => $utility])
	  @include('outfitting.utilitycell', ['class' => 'E', 'utility' => $utility])
	</tr>
	@endforeach
  </tbody>
</table>
