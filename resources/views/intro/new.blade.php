@extends('layout/layout')

@section('title', 'New to Colonia?')

@section('content')

<p>The Colonia region has several significant differences to the Sol region, which it is useful for those thinking of visiting or settling to be aware of. Example CensusBot commands are shown.</p>

<p><a href='{{route('intro.regions')}}'>Quantitative comparisons</a> of Colonia to the other settled regions are also available.</p>
    
<div id='newhere'>
  <div class='newbox'>
	<div>
	  <h2>Location and Travel</h2>

	  @include('intro.botbox', ['commands' => ["!help", "!summary systems"]])
	  
	  <p>The Colonia region is within the Eol Prou sector approximately 22,000 LY from Sol. The most prominent feature is the nebula, which is over 50 LY across, and sits within a dense cluster of B-class stars.</p>
	  
	  <p>A set of 6 surface resupply outposts and a newer set of 3 orbital stations break the journey from Sol into easier and shorter stages, and the neutron highways are well mapped.</p>
	  
	  <p>The populated region now consists of {{$systemcount}} systems and is approximately 100 LY in radius.</p>
	</div>

	<div>
	  <h2>Trading</h2>
	  @include('intro.botbox', ['commands' => ["!report system", "!locate economy name", "!summary economy"]])

	  <p>High-Tech and Service economies are much more common. On the other hand, as there are numerous known earth-like worlds for a population of only {{number_format($totalPopulation)}}, there are no Terraforming economies. Colony economies are also very rare - in general, Tourism economies have taken their place.</p>

	  <p>None of the systems have a high population so supply and demand levels for goods are generally low - however, many of the outlying systems also have low traffic levels, so surpluses and deficits can build up.</p>

    <p>Decent profits can be made on relatively short journeys for those who know where to look - use the <a href="{{route('trade')}}">trade helper</a> and <a href="{{route('reserves')}}">commodity reserves</a> pages to help find routes, and the <a href="{{route('reports.traffic')}}">traffic report</a> to prioritise less serviced markets. The additional profits enabled in the Sol bubble through the policies of particular Powers are of course not available.</p>

	  <p>There is only one rare good - Jaques Quinentian Stills - and the radius of the Colonia region is too small for sales to be profitable anywhere locally.</p>

	</div>

	<div>
	  <h2>Exploration</h2>

	  @include('intro.botbox', ['commands' => ["!cartographics grav pad dist"]])
	  
	  <p>In the early days of Colonia cartographics services were rare. Nowadays, a wide range of stations provide these services - for historical reasons, the <a href="{{route('stations.show', 2)}}">Colonia Hub</a> surface base in Colonia and the <a href="{{route('stations.show', 7)}}">Colonia Dream</a> Coriolis in Ratraii do not.</p>

	  <p>The Colonia region is near to numerous larger and planetary nebula in the Festival Grounds area, and is just above a large neutron field. As the most centrally-located known human colony, and the best equipped one outside Sol, it makes an excellent base for expeditions to most of the galaxy.</p>

	</div>

	<div>
	  <h2>Mining</h2>

	  @include('intro.botbox', ['commands' => ["!locate feature metallic rings", "!locate state Boom"]])
	  
	  <p>The briefness of human occupation of the region means that all mineral reserves are Pristine in quality. The majority of inhabited systems have some mining opportunities, though relatively few have metallic asteroids. Mining options are shown for inhabited systems in the system catalogue, though experienced miners may find better opportunities in nearby uninhabited systems.</p>

	  <p>Finding a booming economy to sell Painite is also easy.</p>

	</div>

	<div>
	  <h2>Combat</h2>

	  @include('intro.botbox', ['commands' => ["!locate feature High RES", "!locate state War", "!locate facility broker"]])
	  
	  <p>The usual range of combat hotspots are available - nav beacons, resource extraction sites and combat zones. There are also two unauthorised installations in the <a href='{{route('systems.show', 47)}}'>Kojeara</a> system providing some more interesting scenery for bounty hunting.</p>

	  <p>As there are no local superpower-aligned factions, superpower bounties must be sold using an Interstellar Factors (aka Broker) service. There is a permanent one in Colonia (except during Lockdown), and temporary ones are often set up in systems affected by War.</p>
	</div>

	<div>
	  <h2>Missions</h2>
	  @include('intro.botbox', ['commands' => ["!mission system"]])

	  <p>Most mission types are available somewhere in Colonia, but the variety and quantity of missions differs a lot from system to system. The following major differences apply:</p>

	  <ul>
		<li>"Tour" VIP passenger missions are not available, as all local tourist beacons are currently in a single system</li>
		<li>"Famous explorer" passenger missions are very common, but due to the distribution of distant tourist beacons, mostly go to systems within 500 LY of Sol. Missions to Sag A*, Beagle Point or other deep space destinations do exist but may require visiting several passenger boards to find.</li>
	  </ul>

	  <p>The <a href="{{route('map')}}">map</a> is able to show which systems are within 15 LY of other systems - systems with more of these links usually (though not always) have more varied mission availability.</p>

	  <p>In addition to these short range links:</p>
	  <ul>
		<li>All systems may generate mid-range missions to Colonia, Ratraii, Ogmar or Tenjin.</li>
		<li>Tourism economies may sometimes generate long-range data courier and one-way passenger missions to systems in the Sol bubble.</li>
	  </ul>
	  
	</div>
	
  </div>
  <div class='newbox'>
	
	<div>
	  <h2>Outfitting and Shipyards</h2>
	  @include('intro.botbox', ['commands' => ["!locate facility high-quality"]])

	  <p>Colonia has slightly limited options in this area, though they are improving over time. The following are currently available, generally in those stations containing <a href='{{route('stations.index')}}#high-qual'>High-Quality Outfitting</a>. Prices are often 20% higher than in the Sol bubble.</p>

	  <ul>
		<li>Mining-related equipment, all sizes, A-grade</li>
		<li>Fuel scoops and AFMUs, A-grade</li>
		<li>Other core and optional internals, most sizes, A-, B- or C-grade maximum depending on size (A-rated size 3 or below, B- or C- at size 4 or above)</li>
		<li>All weapons, all sizes</li>
		<li>All independent ship hulls</li>
	  </ul>
	  <p><a href='{{route('outfitting')}}'>A full list of local availability</a> is provided.</p>
	  
	  <p>Most large A-rated items must be obtained in the Sol bubble and either flown or transferred here. Transfer costs are approximately 130% of the base price, and the transfer will take a little over 60 hours.</p>

	  <p>Colonia Hub is currently the only station providing most AX anti-Thargoid weapons, though at present there is no sign of a Thargoid presence. The Shutdown Field Neutraliser is common enough to appear at several Military and High-Tech surface ports.</p>
	  
	</div>

    <div>
	  <h2>Engineering</h2>

	  <p>Colonia has no engineers, but if blueprints have been pinned, remote workshops are available and can be used to engineer locally-bought modules.</p>

	  <p>While the system populations at under 1 million would normally be insufficient to support Material and Tech Brokers, there is one of each present:</p>
	  <ul>
		<li>Data: Colonia Dream, Ratraii</li>
		<li>Raw: TolaGarf's Junkyard, Kojeara</li>
		<li>Manufactured: Foster Terminal, Coeus</li>
		<li>Human Tech: Bolden's Enterprise, Tir</li>
		<li>Guardian Tech: Jaques Station, Colonia</li>
	  </ul>

	  <p>In addition to normal sources of engineering materials, the Unauthorised Installations in Kojeara have satellites which can be scanned for some data, and miscellaneous manufactured materials (as well as some cargoes required by the Tech Brokers) can be found in the debris around the wrecked bulk carrier in Luchtaine.</p>
    </div>
    
	<div>
	  <h2>Factions and Politics</h2>
    @include('intro.botbox', ['commands' => ["!faction faction", "!influence faction/system", "!summary reach", "!traffic system", "!expansion faction", "!expansionsto system", "!history faction/date/system/station"]])

	  <p>The settlement of Colonia has led to an extremely unusual distribution of factions. Major differences from the Sol bubble include:</p>
	  <ul>
		<li>All factions are Independent. There are no superpowers in the region.</li>
		<li>There is a very strong bias towards the 'Cooperative' faction type, which is rare in the Sol bubble.</li>
		<li>Each system has one or occasionally two home factions. Other factions have expanded there to fill the remaining space. Retreats are therefore more common.</li>
		<li>The Colonia Council faction has been placed as an initial (non-native) faction in most systems. It is currently present in over 50 systems in the region, plus more along the highway.</li>
		<li>Over half of the factions are player-founded, mostly through the Colonia Expansion Initiative. While the Sol bubble has approximately one player faction for every 25 systems, here the ratio is one player faction for every two systems.</li>
		<li>Systems have low NPC population levels but often relatively high player population levels, which allows for rapid changes in influence levels.</li>
		<li>The area does not fall within any Powerplay bubbles and it is in practice impossible for any power in the Sol bubble to accumulate enough CC to expand here.</li>
	  </ul>

	  <p>Two of the systems - Colonia and Ratraii - are restricted. Factions may not expand into those systems, and factions already present may not fight for control of assets.</p>

	  <p>Criminals will be transported to the Odin's Crag detention facility in Eol Prou LW-L c8-127. This also covers the Kashyapa highway system, with Polo Harbour marking the start of the Gandharvi jurisdiction.</p>
	</div>
	<div>
	  <h2>Other points of interest</h2>

	  <p>Colonia contains some unique or rare points of interest.</p>

	  <p><a href="{{route('stations.show', 62)}}">Foster Terminal</a> is a dockable megaship embedded in an ice ring. There are also three asteroid bases - <a href="{{route('stations.show', 56)}}">TolaGarf's Junkyard</a>, <a href="{{route('stations.show', 64)}}">Moore's Charm</a> and <a href="{{route('stations.show', 74)}}">Robardin Rock</a>. A second megaship, the <a href="{{route('stations.show', 62)}}">Dove Enigma</a>, orbits near Colonia following the <a href="https://forums.frontier.co.uk/showthread.php/398466-Enigma-Expedition-to-Colonia-for-Charity-and-for-with-CMDR-DoveEnigma13">Enigma Expedition</a>.</p>

	  <p><a href="{{route('systems.show', 13)}}">Magellan</a> is one of the few inhabited systems with a neutron star primary.</p>

	  <p><a href='https://forums.frontier.co.uk/showthread.php/303036-Fungal-Life-Found-on-Colonia-3-C-A'>Fungal life was discovered</a> in Colonia on planet 3 C a at <code>47.21 x -174.16</code>, though has since disappeared. There is a listening post near Colonia 2 which points to a crashed T-9 at <code title='The listening post says 29.24 x 39.14, incorrectly'>39.14 x 29.24</code> on Colonia 5 E a. There is a persistent distress call from a wrecked bulk cargo ship near Moore's Charm in Luchtaine.</p>

      <p>A rare glowing green gas giant can be found in Eol Prou KW-L c8-32.</p>

      <p>The infamous <a href="https://forums.frontier.co.uk/showthread.php/357172-Challenge-to-land-on-the-world-of-death">World of Death</a> (Spoihaae XE-X d2-9), a landable body which passes through the cone of a white dwarf, is around 600 LY away.</p>
	</div>
  </div>
</div>
	
@endsection
