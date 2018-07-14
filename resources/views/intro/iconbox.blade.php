<div class='iconbox'>
  <div>
	<h2>{{$title}}</h2>
	<ul>
	  @foreach ($entries as $item)
	  <li>
		@include($item->icon)
		{{$item->name}}
	  </li>
	  @endforeach
	</ul>
  </div>
</div>
