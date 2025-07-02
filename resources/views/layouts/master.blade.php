@extends('layouts.app')
@section('app-content')
    @include('layouts.navbar')
    <div class="container mt-5">
        @yield('content')
    </div>
@endsection
