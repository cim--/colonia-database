<table class='table table-bordered'>
  <thead>
	<tr>
	  <th>Ship</th>
	  @include ('outfitting.armourheadcell', ['mtype' => $armours->where('description', 'Lightweight Alloy')->first()])
	  @include ('outfitting.armourheadcell', ['mtype' => $armours->where('description', 'Reinforced Alloy')->first()])
	  @include ('outfitting.armourheadcell', ['mtype' => $armours->where('description', 'Military Grade Composite')->first()])
	  @include ('outfitting.armourheadcell', ['mtype' => $armours->where('description', 'Mirrored Surface Composite')->first()])
	  @include ('outfitting.armourheadcell', ['mtype' => $armours->where('description', 'Reactive Surface Composite')->first()])
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
