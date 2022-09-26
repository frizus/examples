@extends('layouts.app')
@section('title', 'Производные ассеты')

@section('content')
    @include('derivative.filter')
    @include('derivative.items')
    @include('paginate', ['records' => $rows])
@endsection
