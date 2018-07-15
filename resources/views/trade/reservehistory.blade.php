@extends('layout/layout')

@section('title')
History of <a href='{{route('reserves.commodity', $commodity->id)}}'>{{$commodity->displayName()}}</a>
@endsection
@section('headtitle')
History of {{$commodity->displayName()}}
@endsection

@section('content')

  
@if ($chart)
<p><strong>Notice: supply and demand axes may have very different scales.</strong></p>
@include('layout/chart')
@else
<p>No trade data is available for the specified time period.</p>
@endif

{!! Form::open(['route' => ['reserves.commodity.history', 'commodity' =>$commodity->id], 'method' => 'GET']) !!}

{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}

{!! Form::submit('Set date range') !!}
<a href='{{route('reserves.commodity.history', ['commodity' =>$commodity->id])}}'>(Show all data)</a>
{!! Form::close() !!}
    
@endsection
