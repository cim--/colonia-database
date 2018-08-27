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
      @if (isset($extras))
      @foreach ($extras as $name => $icon)
	  <li>
		@include($icon)
		{{$name}}
	  </li>
	  @endforeach
	  @endif
	</ul>
  </div>
</div>
