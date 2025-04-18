@extends('doctor.layouts.master')

@section('content')
@php
    $sidenav = file_get_contents(resource_path('views/doctor/partials/sidenav.json'));
@endphp

    <div class="page-wrapper default-version">
        @include('doctor.partials.sidenav')
        @include('doctor.partials.topnav')
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                @include('doctor.partials.breadcrumb')
                @yield('panel')
            </div>
        </div>
    </div>

@endsection

