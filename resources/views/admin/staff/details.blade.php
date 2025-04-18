@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="row gy-4">
                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                    link="javascript:void(0)"
                    style="7"
                    icon="las la-handshake"
                    title="Total Appointments"
                    value="{{ $totalAppointments }}"
                    bg="primary"
                    />
                </div>
                <!-- dashboard-w1 end -->

                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                    link="javascript:void(0)"
                    style="7"
                    icon="las la-handshake"
                    title="Total New Appointments"
                    value="{{ $newAppointments }}"
                    bg="9"
                    />
                </div>
                <!-- dashboard-w1 end -->

                <div class="col-xxl-3 col-sm-6">
                    <x-widget
                    link="javascript:void(0)"
                    style="7"
                    icon="las la-check-circle"
                    title="Total Done Appointments"
                    value="{{ $doneAppointments }}"
                    bg="13"
                    />
                </div>
                <!-- dashboard-w1 end -->
                <div class="col-xxl-3 col-sm-6">

                    <x-widget
                    link="javascript:void(0)"
                    style="7"
                    icon="las la-trash"
                    title="Total Trashed Appointments"
                    value="{{ $trashedAppointments }}"
                    bg="red"
                    />
                </div>
                <!-- dashboard-w1 end -->
            </div>
            <div class="d-flex flex-wrap gap-3 mt-4">
                <div class="flex-fill">
                    <a href="{{ route('admin.staff.login.history', $staff->id) }}"
                        class="btn btn--primary btn--shadow w-100 btn-lg">
                        <i class="las la-history"></i>@lang('Login History')
                    </a>
                </div>
                <div class="flex-fill">
                    <a href="{{ route('admin.staff.notification.log', $staff->id) }}"
                        class="btn btn--warning btn--shadow w-100 btn-lg">
                        <i class="las la-envelope"></i>@lang('Notification Logs')
                    </a>
                </div>

                <div class="flex-fill">
                    <a href="{{ route('admin.staff.login', $staff->id) }}" target="_blank"
                        class="btn btn--primary btn--gradi btn--shadow w-100 btn-lg">
                        <i class="las la-sign-in-alt"></i>@lang('Login as Staff')
                    </a>
                </div>
            </div>

            <div class="card mt-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Information of') {{ $staff->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.staff.store', $staff->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>@lang('Image')</label>
                                        <x-image-uploader name="image"
                                        :imagePath="getImage(getFilePath('staffProfile') . '/' . @$staff->image, getFileSize('staffProfile'))"
                                        :size="getFileSize('staffProfile')" class="w-100" id="uploadLogo" :required="false" />

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>@lang('Name')</label>
                                            <input type="text" name="name" value="{{ $staff->name }}"
                                                class="form-control " required />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>@lang('Username')</label>
                                            <input type="text" name="username" value="{{ $staff->username }}"
                                                class="form-control " required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>@lang('E-mail')</label>
                                            <input type="text" name="email" value="{{ $staff->email }}"
                                                class="form-control " required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Mobile')
                                                <i class="fa fa-info-circle text--primary" title="@lang('Add the country code by general setting. Otherwise, SMS won\'t send to that number.')">
                                                </i>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ gs('country_code') }}</span>
                                                <input type="number" name="mobile"
                                                    value="{{ str_replace(gs('country_code'), '', $staff->mobile) }}"
                                                    class="form-control " autocomplete="off" required>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>@lang('Address') </label>
                                            <textarea name="address" class="form-control">{{ $staff->address }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
<x-back route="{{ route('admin.staff.index') }}"/>
  
@endpush
