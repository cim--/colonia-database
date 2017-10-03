@extends('layout/layout')

@section('title', 'About the Colonia Census')

@section('content')

<p>The Colonia Census aims to provide current, accurate and comprehensive information about the inhabited systems and population of the Colonia Region, for the use of all residents and visitors.</p>

<p>If you are new to the region, or thinking of visiting, then the <a href="{{route('intro.new')}}">new to Colonia</a> page will answer some common questions.</p>

<p>Discussion of the census data, its collection, or suggestions for additional features can take place on the <a href="">Discord Server</a>. This server also contains a bot (CensusBot) who can answer various questions about the region - extensive queries can be carried out by private message or in the <code>#bot</code> channel.</p>

<p>If you notice the appearance of new systems, stations or factions, or find a system feature (e.g. a RES or an installation) not on the list, please mention it in the <code>#colonia-census</code> channel.</p>

<h2>Bot commands</h2>
<p>Census bot commands are all prefixed by a '!' - so, for example, to use the 'system' command, start your message with <kbd>!system</kbd>. Where a faction, system, or station name is required, typing the first letters is enough - e.g. <kbd>!faction Priv</kbd> instead of <kbd>!faction Privateer's Alliance Expeditionary Force</kbd></p>

<ul>
  <li><kbd>!help</kbd>: sends a help and usage message</li>
  <li><kbd>!system</kbd> <samp>name</samp>: gives information on the named system</li>
  <li><kbd>!station</kbd> <samp>name</samp>: gives information on the named station</li>
  <li><kbd>!faction</kbd> <samp>name</samp>: gives information on the named faction</li>
  <li><kbd>!influence</kbd> <samp>name</samp>: gives the current influence levels for the named faction or system</li>
  <li><kbd>!influence</kbd> <samp>date</samp> <samp>name</samp>: gives the influence levels on the specified date. The date must be in the YYYY-MM-DD format e.g. 3303-05-17</li>
  <li><kbd>!report</kbd> <samp>name</samp>: gives the current traffic and crime levels for the named system</li>
  <li><kbd>!report</kbd> <samp>date</samp> <samp>name</samp>: gives the report for the specified date. The date must be in the YYYY-MM-DD format e.g. 3303-05-17</li>
  <li><kbd>!locate feature</kbd> <samp>name</samp>: finds systems containing a particular feature (e.g. ring types). Leave 'name' out to get a list of possibilities.</li>
  <li><kbd>!locate facility</kbd> <samp>name</samp>: finds stations providing a particular facility (e.g. outfitting). Leave 'name' out to get a list of possibilities.</li>
  <li><kbd>!locate economy</kbd> <samp>name</samp>: finds stations with a particular economy. Leave 'name' out to get a list of possibilities.</li>
  <li><kbd>!locate government</kbd> <samp>name</samp>: finds stations run by a particular government type. Leave 'name' out to get a list of possibilities.</li>
  <li><kbd>!locate state</kbd> <samp>name</samp>: finds stations with a particular active state. Leave 'name' out to get a list of possibilities.</li>
  <li><kbd>!expansion</kbd> <samp>faction</samp>: shows the faction's next expansion targets, expanding from its home system.</li>
  <li><kbd>!expansion</kbd> <samp>faction</samp> <code>;</code> <samp>system</samp>: shows the faction's next expansion targets, expanding from the specified system. The ';' is used to separate faction and system names.</li>
  <li><kbd>!expansion</kbd> <samp>system</samp>: shows the controlling faction's next expansion targets from this system.</li>
  <li><kbd>!expansionsto</kbd> <samp>system</samp>: shows the factions likely to expand into this system in the near future.</li>
  <li><kbd>!missions</kbd> <samp>name</samp>: shows the systems within 15 LY that make most typical mission targets.</li>
  <li><kbd>!cartography</kbd> <samp>max-gravity</samp> <samp>pad-size</samp> <samp>max-dist</samp>: returns possible exploration data sale locations given a max gravity (use 0 for orbitals only), minimum landing pad size, and maximum distance from the primary star - e.g. <kbd>!cartography 0.3 L 1000</kbd></li>
  <li><kbd>!summary</kbd> <samp>name</samp>: returns a regional summary. Available summaries are population, traffic, crimes, bounties, systems, stations, economy, government, state and reach.</li>
  <li><kbd>!history</kbd> <samp>filter</samp>: returns the relevant history entries. The filter can be a date, system, station or faction. If no filter is set it returns the current tick.</li>
</ul>

<p>If you would like to add CensusBot to your server, you can use the
link in the topic of the <code>#bot</code> channel. CensusBot only
needs and uses 'read message' and 'write message' permissions. </p>

<p>CensusBot does not and will never log the requests it processes
or any other messages it sees.</p>

@endsection
