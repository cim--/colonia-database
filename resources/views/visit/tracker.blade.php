<div id='{{$id}}' class='tracker'>
  <div class='trackrow'>
	<div class='category'>{{$label}}</div>
	<div class='progressbar'>
	  <div class='recorded'></div>
	</div>
	<div class='numbers'>
	  <span class='tracked'>0</span> / <span class='total'>{{$entries->count()}}</span>
	</div>
  </div>
  <div class='trackrow itemlist'>
	<div class='spacer'>&nbsp;</div>
	<div class='items'>
	  @foreach ($entries as $entry)
	  <span data-domain='{{strtolower($label)}}' data-number='{{$entry->id}}'>{{$entry->displayName()}}<span class='marker'>&nbsp;</span></span>
	  @endforeach
	</div>
  </div>
</div>

