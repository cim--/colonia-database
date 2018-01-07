<table class='table table-bordered'>
  <thead>
	<tr>
	  <th>Ship</th>
	  <th>Lightweight</th>
	  <th>Reinforced</th>
	  <th>Military</th>
	  <th>Mirrored</th>
	  <th>Reactive</th>
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