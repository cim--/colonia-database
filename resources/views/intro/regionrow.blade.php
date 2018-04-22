	<tr>
	  <th scope='row'>{{$label}}</th>
	  <td colspan='2' class='native'>{{number_format($here)}}</td>
	  @foreach ($regions as $region)
      <td colspan='2' class='other'>{{number_format($region->$there)}}</td>
	  @endforeach
	</tr>
