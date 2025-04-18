@extends('staff.layouts.master')

@section('content')
@php
    $sidenav = file_get_contents(resource_path('views/staff/partials/sidenav.json'));
@endphp

    <div class="page-wrapper default-version">
        @include('staff.partials.sidenav')
        @include('staff.partials.topnav')
        <div class="body-wrapper">
            <div class="bodywrapper__inner">
                @include('staff.partials.breadcrumb')
                @yield('panel')
            
            </div>
        </div>
    </div>

@endsection

