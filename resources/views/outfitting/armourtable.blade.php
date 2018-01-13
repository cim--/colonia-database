<table class='table table-bordered'>
  <thead>
	<tr>
	  <th>Ship</th>
	  <th>
		<a href='{{route('outfitting.moduletype', $armours->where('description', 'Lightweight Alloy')->first()->id)}}'>Lightweight</a>
	  </th>
	  <th>
		<a href='{{route('outfitting.moduletype', $armours->where('description', 'Reinforced Alloy')->first()->id)}}'>Reinforced</a>
	  </th>
	  <th>
		<a href='{{route('outfitting.moduletype', $armours->where('description', 'Military Grade Composite')->first()->id)}}'>Military</a>
	  </th>
	  <th>
		<a href='{{route('outfitting.moduletype', $armours->where('description', 'Mirrored Surface Composite')->first()->id)}}'>Mirrored</a>
	  </th>
	  <th>
		<a href='{{route('outfitting.moduletype', $armours->where('description', 'Reactive Surface Composite')->first()->id)}}'>Reactive</a>
	  </th>
	</tr>
  </thead>
  <tbody>
	@foreach ($shiptypes as $ship)
	<tr>
	  <td>{{$ship}}</td>
	  <td>
		@include('outfitting.armourcell', ['class' => 'Lightweight Alloy', 'ship' => $ship, 'armours' => $armours])
	  </td>
	  <td>
		@include('outfitting.armourcell', ['class' => 'Reinforced Alloy', 'ship' => $ship, 'armours' => $armours])
	  </td>
	  <td>
		@include('outfitting.armourcell', ['class' => 'Military Grade Composite', 'ship' => $ship, 'armours' => $armours])
	  </td>
	  <td>
		@include('outfitting.armourcell', ['class' => 'Mirrored Surface Composite', 'ship' => $ship, 'armours' => $armours])
	  </td>
	  <td>
		@include('outfitting.armourcell', ['class' => 'Reactive Surface Composite', 'ship' => $ship, 'armours' => $armours])
	  </td>
	</tr>	
	@endforeach
  </tbody>
</table>
