<table class='table table-bordered'>
  <thead>
	<tr>
	  <th rowspan='2'>Weapon</th>
	  <th colspan='4'>Fixed</th>
	  <th colspan='4'>Gimballed</th>
	  <th colspan='4'>Turret</th>
	</tr>
	<tr>
	  @for ($i=1;3>=$i;$i++)
	  <th>Small</th>
	  <th>Medium</th>
	  <th>Large</th>
	  <th>Huge</th>
	  @endfor
	</tr>
  </thead>
  <tbody>
	@foreach ($weapons as $weapon)
	<tr>
	  <td>{{$weapon->description}}</td>
	  @foreach (['Fixed', 'Gimballed', 'Turreted'] as $mount)
	  @include('outfitting.weaponcell', ['size' => 1, 'mount' => $mount, 'weapon' => $weapon])
	  @include('outfitting.weaponcell', ['size' => 2, 'mount' => $mount, 'weapon' => $weapon])
	  @include('outfitting.weaponcell', ['size' => 3, 'mount' => $mount, 'weapon' => $weapon])
	  @include('outfitting.weaponcell', ['size' => 4, 'mount' => $mount, 'weapon' => $weapon])
	  @endforeach
	</tr>
	@endforeach
  </tbody>
</table>
	  
	  
