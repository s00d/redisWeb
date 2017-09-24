@extends('area_index.app')
@section('title', trans('common.title_after'))

@section('js')

@endsection

@section('css')
    <link href="/css/nprogress.css" rel="stylesheet" type="text/css">
    <link href="/css/vanillatoasts.css" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div id="app"></div>

    <script src="{{ mix('/js/app.js') }}"></script>
@endsection


