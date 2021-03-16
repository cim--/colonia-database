@extends('layout/layout')

@section('title')
Activity Trends
@endsection

@section('content')

<p>The graph shows the total traffic, crime and bounties activities across the region. Activity is estimated for most days due to only partial collection of report data being practical.</p>

<ul>
  <li><strong>Current Traffic:</strong> {{number_format($traffic)}} (28-day average {{number_format($avgtraffic)}})</li>
  <li><strong>Current Crimes:</strong> {{number_format($crimes)}} (28-day average {{number_format($avgcrimes)}})</li>
  <li><strong>Current Bounties:</strong> {{number_format($bounties)}} (28-day average {{number_format($avgbounties)}})</li>
</ul>

@include('layout/chart')

{!! Form::open(['route' => ['history.trends'], 'method' => 'GET']) !!}
{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}
{!! Form::submit('Set date range') !!}
<a href='{{route('history.trends')}}'>(Show all data)</a>
{!! Form::close() !!}

    
@endsection
