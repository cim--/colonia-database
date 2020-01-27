@extends('layout/layout')

@section('title')
History of <a href='{{route('reserves.commodity', $commodity->id)}}'>{{$commodity->displayName()}}</a>
@endsection
@section('headtitle')
History of {{$commodity->displayName()}}
@endsection

@section('content')

  
@if ($chart)
@if ($mode == 'volume')
<p><a href='{{route('reserves.commodity.pricehistory', ['commodity' =>$commodity->id])}}'>See pricing history</a></p>
<p><strong>Notice: supply and demand axes may have very different scales.</strong> Data near major economic shifts may show increased volatility.</p>
@else
<p><a href='{{route('reserves.commodity.history', ['commodity' =>$commodity->id])}}'>See volume history</a></p>
<p>Partial data collection may result in less variability shown than occurred. Data near major economic shifts may show increased volatility.</p>
@endif
@include('layout/chart')
@else
<p>No trade data is available for the specified time period.</p>
@endif

@if ($mode == 'volume')
{!! Form::open(['route' => ['reserves.commodity.history', 'commodity' =>$commodity->id], 'method' => 'GET']) !!}
@else
{!! Form::open(['route' => ['reserves.commodity.pricehistory', 'commodity' =>$commodity->id], 'method' => 'GET']) !!}
@endif

{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}

{!! Form::submit('Set date range') !!}
<a href='{{route('reserves.commodity.history', ['commodity' =>$commodity->id])}}'>(Show all data)</a>
{!! Form::close() !!}
    
@endsection
