@extends('layout/layout')

@section('title', 'The story of Colonia')

@section('content')

<div id='historyboxes'>
<div class='historybox'>
<h2>Accidental arrival</h2>

<p>In the 3200s, a long-lived cyborg named Jaques bought an Orbis-class starport and refitted it for interstellar travel. Following journeys in that century, they returned to human space in 3301. After a couple of years, they had refitted the station with the latest frameshift drives, and were ready to begin travelling again - this time, to the opposite end of the galaxy at Beagle Point.</p>

<p>The <a href='{{route('factions.show',74)}}'>Fuel Rats</a> arranged for the vast quantities of fuel required. Loaded with over 7 million tonnes of fuel, Jaques prepared the station for the jump, entering hyperspace in early June 3302. However, shortly before departure, a visitor to the station had delivered a significant number of "Unknown Artifacts" - now recognised as the corrosive "Thargoid Sensor".</p>

<p>Interference from the Thargoid technology caused major system failures on the station during the jump, forcing Jaques to abort and drop from hyperspace before the station was destroyed. Severely damaged, and with insufficient power to send a proper distress call, the station exited hyperspace in the Eol Prou RS-T d3-94 system, in a lightly-charted region of space near the galactic core.</p>

<p>Searches for the station were immediately launched, but were not successful.</p>

</div>

<div class='historybox'>
  <h2>Rediscovery and rescue</h2>

  <p>On 29 June 3302, almost four weeks after leaving the bubble, Jaques was rediscovered by Commander Cly, who had discovered anomalous cartographics data resulting from low-power automated transmitters on the station. While garbled, it was enough to hint at some human presence in the system, and Cly flew out to investigate.</p>

  <p>Discovering Jaques and the station to be intact but severely damaged, he reported his find to several organisations, who launched major rescue convoys, flying out relief supplies, repair tools, and meta-alloys to shield against the effects of the Thargoid sensors.</p>

  <p>The majority of the repairs were completed quickly, but the damage to the frame shift drive was found to be considerably more severe - <a href='{{route('stations.show', 1)}}'>Jaques Station</a> would be remaining in its present location for the foreseeable future.</p>

</div>

<div class='historybox'>
  <h2>Initial colonisation</h2>

  <p>The presence of an operational starport 22,000 light years from Sol - at a time when the furthest previous human settlement had been in the Pleiades - sparked great interest in founding a new colony around it, especially from those least content with the politics and rivalries of the Sol bubble. The system was renamed Colonia.</p>

  <p>A surface base, <a href='{{route('stations.show', 2)}}'>Colonia Hub</a>, was constructed, opening on 8 September 3303. It was operated by the new <a href='{{route('factions.show', 5)}}'>Colonia Council</a>, formed from the representatives of various groups to allow colonisation to proceed in a managed fashion.</p>

  <p>A series of six small surface outposts were constructed between Sol and Colonia to make transportation of supplies more straightforward. With only limited facilities, they nevertheless were successful. Meanwhile, the Council used its funds to purchase a significant amount of mining gear, encouraging pilots to provide the raw materials to allow production of further stations.</p>

  <p>On 25 October 3302, stations in <a href='{{route('systems.index')}}#"core 1"'>eight systems around Colonia</a> were opened, mostly with lightweight outposts providing basic facilities for partial local production of necessities, but additionally the parts for the <a href='{{route('stations.show', 7)}}'>Colonia Dream</a> station were transported from the bubble and assembled - it would be much longer before the majority of services aboard were made operational.</p>
  
</div>

<div class='historybox'>
  <h2>The Colonia Expansion Initiative</h2>

  <p>The growing feasibility of a distant colony increased the demand for further colonisation, but the Council were aware that growing too fast could be difficult. Applicants were therefore expected to demonstrate their commitment to the region by transporting goods, and many existing and new organisations formed to take on the challenge.</p>

  <p>In total six rounds of settlement were carried out under this programme, in the early half of 3303, with 46 systems being marked for development. (At the present time, 6 of the qualifying groups have been faced with difficulties and have not yet taken up their systems). A basic surface base was constructed in these systems, with the economic output of these bases helping to stabilise the region.</p>

  <p>Some of these organisations have since <a href='{{route('reports.control')}}'>taken on responsibility for operation of additional installations</a> and <a href='{{route('reports.reach')}}'>become widely supported by the colonists</a>, while others have been content to simply operate and maintain their own base.</p>

</div>

<div class='historybox'>
  <h2>Criminal Conflicts</h2>

  <p>While inevitably criminal elements had been a part of the colony from its early days, they had largely been kept contained, until they seized an opportunity with the founding of <a href='{{route('systems.show', 47)}}'>Kojeara</a> in 25 April 3303 to provide a larger recycling facility for the growing colony's industrial waste.</p>

  <p>On 18 May 3303, they launched a significant attack against the system from unauthorised installations around the outer gas giants. The <a href='{{route('factions.show', 41)}}'>Junkyard Dogs</a> called for assistance, and in a series of battles the attack was beaten off.</p>

</div>

<div class='historybox'>
  <h2>Economic self-sufficiency</h2>

  <p>Difficulties in providing supplies, especially the limited agricultural supplied by the <a href='{{route('stations.show', 8)}}'>Vitto Orbital</a> outpost, led the Council to approach a number of entrepreneurs to operate stations in the region. Mostly junior but ambitious staff from existing organisations, they worked with the Council and funders from the bubble to construct <a href='{{route('systems.index')}}#"core 3"'>sixteen new orbital stations</a> of varying sizes in June 3303.</p>

<p>A month later, additional outposts were constructed in the larger of these systems, to diversify the economies, install the latest production tools, and provide additional landing space for ships.</p>

  <p>Organising the economy with a focus on high-tech manufacturing, services, and tourism, the Colonia Council was able to take advantage of its 'clean start' position and lack of legacy infrastructure to develop <a href='{{route('outfitting')}}'>major shipyards</a> considerably better than most Sol bubble facilities. Independent manufacturers were happy to license their designs - at a slightly higher cost - to supply what was by now becoming a major exploration and mining hub.</p>
</div>

<div class='historybox'>
  <h2>Attempted Coup</h2>

  <p>Having failed previously in Kojeara, the criminal organisations largely returned to the background, and attempted to establish a base on the edge of the region in <a href='{{route('systems.show', 71)}}'>Carcosa</a>, hollowing out a small moon to become <a href='{{route('stations.show', 74)}}'>Robardin Rock</a> and importing their own shipyard tools from an as-yet-unidentified backer in the Sol bubble.</p>

  <p>Their plan to build up forces quietly was not a success - the <a href='{{route('factions.show', 17)}}'>Explorers' Nation</a> group was patrolling the upper fringes, and launched an immediate assault, capturing Robardin Rock on 30 August 3303.</p>

  <p>With their remaining minor outpost under threat as well, they launched a desperate attack on Colonia itself with the forces they had so far assembled, trying to take control of the Colonia Hub facility and its production facilities.</p>

  <p>Pilots responded to the emergency rapidly, and with their resupply lines cut-off, the majority of their fleet surrended to the authorities after being outnumbered 15 to 1 and surrounded.</p>
  
</div>


<div class='historybox'>
  <h2>Refugees and Threats</h2>
  
  <p>With Colonia economically self-sufficient and in many respects outstripping the original bubble for production efficiency, the Council's focus had originally intended to move to consolidation and development of its existing systems. A small part of this programme was completed separately under the coordination of the <a href='{{route('factions.show', 18)}}'>Galcop Colonial Defence Commission</a> who built <a href='{{route('stations.show', 108)}}'>Whirling Station</a> in late 3303. Partners in the Sol bubble also assisted with the construction of three further resupply bases at Rohini, Gandharvi and Kashyapa.</p>

  <p>However, the majority of the programme was put on hold, following Thargoid attacks on the Pleiades Nebula. While the vast majority of citizens in the Sol bubble believed - at the time! - their leaders' assurances that they were safe from attack, as stations burned and the initial Thargoid scouting fleet moved closer to the bubble, around 500,000 refugees were able to obtain bulk transports, and headed for Colonia. The Council adjusted its development plans, constructing five new outposts to house the refugees and integrate them into the colony's society.</p>

  <p>Concerns that criminal groups - or an organised Sol bubble organisation fleeing the Thargoids - might again attempt to take over the region led to the construction of the <a href='{{route('installations.show', 1)}}'>Colonia Bastion</a> Security Installation in April 3304.</p>
  
</div>

<div class='historybox'>
  <h2>Logistics</h2>

  <p>The need to plan for further refugees, and to maintain the sustainability of the colony, caused the Council to commission a research programme led by Alexei de la Vega. Initial data analysis suggested that while the colony's economy was strong on paper, with adequate production to supply a much larger population, there were likely to be significant logistical issues transporting these goods in future if the Council were to continue to solely rely on independent pilots in personal ships. The resupply bases between Colonia and Sol were considered particularly at threat.</p>

  <p>The Council began by constructing a large number of <a href='{{route('installations.index')}}'>installations</a> to coordinate economic activity and monitor stocks in more detail, as well as purchasing several <a href='{{route('megaships.index')}}'>larger freighters</a> to move cargo in bulk - logistics routes through the region, support and tourism ships for the highway stations, and trade ships to supply some of Colonia's surplus production to the Sol bubble in exchange for the few goods not produced locally. The Colonia system became a major cargo exchange hub for these freighters.</p>

  <p>A large donation by Zachary Rackham through the Council's charitable tax advantage programme also allowed basic shipyards to be constructed at all surface stations.</p>
  
  <p>Additional funding was provided to de la Vega's team, including the construction of a <a href='{{route('installations.show', 31)}}'>purpose-built research facility</a>, to allow more in-depth studies to be carried out and plan for further expansion.</p>

</div>

<div class='historybox'>
  <h2>Development</h2>
  <p>To avoid requiring the potentially destructive use of Earth-like Worlds for farming, and as a quicker alternative to terraforming, the region had been making heavy use of hydroponics, with stations like <a href='{{route('stations.show', 104)}}'>Jonas Station</a> or <a href='{{route('stations.show', 78)}}'>Pilkington Orbital</a> orbiting dead ice worlds and providing food through internal facilities.</p>

  <p>Professor Diana VanCleef recommended to the Council that a major hydroponics facility and research centre be constructed, with the well-defended Randgnid system used to collect supplies. Substantial agricultural orbitals were then constructed in the <a href='{{route('systems.show', 79)}}'>Kinesi</a> system, as well as large habitation facilities to hold additional immigrants to Colonia.</p>

  <p>At the same time, the Council recruited specialist <a href='{{route('engineers.index')}}'>engineers</a> to develop the shipyards and outfitting capabilities of the region further. Their initial facilities were basic, but allowed experimental modifications to be made locally, and received assistance from many pilots to develop their capabilities.</p>

  <p>As the engineering research continued, with the funding of the Council and the support of many independent pilots, they grew to match and in some cases surpass the technology developed in the bubble. As well as experimental modifications, this research also led to the construction of the <a href='{{route('installations.show', 37)}}'>Colonia Applied Research</a> laboratories, transitioning Colonia itself to a high-tech economy and further equipping its fleets.</p>

</div>



<div class='historybox'>
  ...to be continued...
</div>

</div>

@endsection
