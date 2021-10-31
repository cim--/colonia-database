@extends('layout/layout')

@section('title', 'New to Colonia?')

@section('content')

<p>The Colonia region has several significant differences to the Sol region, which it is useful for those thinking of visiting or settling to be aware of. Example CensusBot commands are shown. You can use the <a href='{{route('visit')}}'>visit tracker</a> to keep a record of which parts of Colonia you have visited so far.</p>

<p><a href='{{route('intro.regions')}}'>Quantitative comparisons</a> of Colonia to the other settled regions are also available.</p>
    
<div id='newhere'>
  <div class='newbox'>
	<div>
	  @include('intro.botbox', ['commands' => ["!help", "!summary systems"]])
          @include('intro.new.location')
        </div>

	<div>
	  @include('intro.botbox', ['commands' => ["!report system", "!locate economy name", "!summary economy"]])
          @include('intro.new.trading')
	</div>

	<div>
	  @include('intro.botbox', ['commands' => ["!cartographics grav pad dist"]])
	  @include('intro.new.exploration')
	</div>

	<div>
	  @include('intro.botbox', ['commands' => ["!locate feature High RES", "!locate state War", "!locate facility broker"]])
	  @include('intro.new.combat')
	</div>

	<div>
	  @include('intro.botbox', ['commands' => ["!megaship serial", "!installations system"]])
	  @include('intro.new.piracy')
	  
	</div>
	
	<div>
	  @include('intro.botbox', ['commands' => ["!mission system"]])
	  @include('intro.new.missions')
	</div>
	
  </div>
  <div class='newbox'>

	<div>
	  @include('intro.botbox', ['commands' => ["!locate feature metallic rings", "!locate state Boom"]])
	  @include('intro.new.mining')
	</div>
	
	<div>
	  @include('intro.botbox', ['commands' => ["!locate facility high-quality"]])
	  @include('intro.new.outfitting')
	</div>

	<div>
	  	  @include('intro.new.engineering')
    </div>
    
	<div>
	  <h2>Factions and Politics</h2>
    @include('intro.botbox', ['commands' => ["!faction faction", "!influence faction/system", "!summary reach", "!traffic system", "!expansion faction", "!expansionsto system", "!history faction/date/system/station"]])

	  <p>The settlement of Colonia has led to an extremely unusual distribution of factions. Major differences from the Sol bubble include:</p>
	  <ul>
		<li>All factions are Independent. There are no superpowers in the region.</li>
		<li>There is a very strong bias towards the 'Cooperative' faction type, which is rare in the Sol bubble. Some <a href='{{route('factions.ethos')}}'>government types</a> are unique to Colonia.</li>
		<li>Each system has one or occasionally two home factions. Other factions have expanded there to fill the remaining space. Retreats are therefore more common.</li>
		<li>The Colonia Council faction has been placed as an initial (non-native) faction in most systems, and while it has since Retreated from many of these it still retains a substantial presence.</li>
		<li>Over half of the factions are player-founded, mostly through the Colonia Expansion Initiative. While the Sol bubble has approximately one player faction for every 10 systems, here the ratio is one player faction for every two systems.</li>
		<li>Systems have low NPC population levels but often relatively high player population levels, which allows for rapid changes in influence levels.</li>
		<li>The area does not fall within any Powerplay bubbles and it is in practice impossible for any power in the Sol bubble to accumulate enough CC to expand here, though Sirius and Kumo representatives are present in a minor role.</li>
	  </ul>

	  <p>Two of the systems - Colonia and Ratraii - are restricted. Factions may not expand into those systems, and factions already present may not fight for control of assets.</p>

	  <p>Criminals will be transported to the Odin's Crag detention facility in Eol Prou LW-L c8-127. This also covers the Kashyapa highway system, with Polo Harbour marking the start of the Gandharvi jurisdiction.</p>
	</div>
	<div>
	    @include('intro.new.tourism')
	</div>
	<div>
	    @include('intro.new.highway')
	</div>
  </div>
</div>
	
@endsection
