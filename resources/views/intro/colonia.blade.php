@extends('layout/layout')

@section('title', 'About the Colonia Nebula')

@section('content')

<p>The Colonia Nebula is one of the most interesting regions of the galaxy in terms of history, natural beauty, economics and politics. Use the following links to find out more.</p>

<ul>
  <li><a href="{{route('intro.story')}}">The Story of Colonia</a> - read about how we got this far</li>
  <li><a href="{{route('intro.new')}}">Differences between Colonia and Sol</a> - useful information for new visitors on what to expect out here</li>
  <li><a href='{{route('radio')}}'>Colonia Radio</a> - live information on regional conditions</li>
  <li><a href='https://drive.google.com/file/d/1aXPVsYBYjhMLPp4zu9RJfKKoxZFgP9Iz/view?usp=sharing'>A Tourist Guide to Colonia</a> (PDF) - detailed information on all of Colonia's systems, and their role in the region as a whole.</li>
  <li><a href='https://drive.google.com/file/d/1HOSLLTjNMdVJqTdCjh4MVFIP8eIk8XEG/view?usp=sharing'>A history of the Third Regional Conflict</a> (PDF) - a guide to Colonia's largest political dispute.</li>
</ul>
    
@endsection
