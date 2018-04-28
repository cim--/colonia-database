<table class='regionalcomparison table table-bordered'>
  <thead>
	<tr>
	  <td></td>
	  <th colspan='2' scope='col' class='native'>Colonia</th>
	  @foreach ($regions as $region)
	  <th colspan='2' scope='col' class='other'>{{$region->name}}</th>
	  @endforeach
	</tr>
  </thead>
