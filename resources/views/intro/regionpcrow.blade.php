	<tr>
	  <th scope='row'>{{$label}}</th>
	  <td class='nativel'>{{number_format($here)}}</td>
	  <td class='nativer' title="per capita">{{number_format($here/$totalPopulation, 3)}}</td>
	  @foreach ($regions as $region)
      <td class='otherl'>{{number_format($region->$there)}}</td>
	  <td class='otherr' title="per capita">{{number_format($region->$there/$region->population, 3)}}</td>
	  @endforeach
	</tr>
