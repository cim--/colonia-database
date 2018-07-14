@extends('layout/layout')

@section('title')
History of {{$commodity->displayName()}} at <a href='{{route('stations.showtrade', $station->id)}}'>{{$station->name}}</a>
@endsection
@section('headtitle')
History of {{$commodity->displayName()}} at {{$station->name}}
@endsection

@section('content')

@if ($chart)
@include('layout/chart')
@else
<p>No trade data is available for the specified time period.</p>
@endif

{!! Form::open(['route' => ['stations.showtradehistory', 'station'=>$station->id, 'commodity' =>$commodity->id], 'method' => 'GET']) !!}

{!! Form::label('minrange', 'Start date') !!}
{!! Form::text('minrange', App\Util::formDisplayDate($minrange)) !!}
{!! Form::label('maxrange', 'End date') !!}
{!! Form::text('maxrange', App\Util::formDisplayDate($maxrange)) !!}

{!! Form::submit('Set date range') !!}
<a href='{{route('stations.showtradehistory', ['station'=>$station->id, 'commodity' =>$commodity->id])}}'>(Show all data)</a>
{!! Form::close() !!}
    
@endsection
