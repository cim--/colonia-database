@extends('layout/layout')

@section('title', 'Icons Key')

@section('content')

<p>To shorten the display, icons are used to represent various
features and classifications. If you have a mouse, hovering over an
icon will give a more detailed description. A key of all icons is
provided below.</p>

<div id='iconlist'>
  <div class='iconrow'>

	@include('intro.iconbox', ['title'=>'Economies', 'entries' => $economies])
	@include('intro.iconbox', ['title'=>'Station Facilities', 'entries' => $facilities->where('type', 'Station')])
	@include('intro.iconbox', ['title'=>'System Facilities', 'entries' => $facilities->where('type', 'System')])
  </div>
  <div class='iconrow'>
	@include('intro.iconbox', ['title'=>'Governments', 'entries' => $governments])
	@include('intro.iconbox', ['title'=>'Installations', 'entries' => $installations])
	@include('intro.iconbox', ['title'=>'Megaships', 'entries' => $megaships])
  </div>
  <div class='iconrow'>
	<div class='iconbox'>
	  <div>
		<h2>Miscellaneous</h2>
		<ul>
		  <li>
			@include('icons.misc.strategic')
			Strategic Asset
		  </li>
		</ul>
	  </div>
	</div>
  </div>

</div>

@endsection
