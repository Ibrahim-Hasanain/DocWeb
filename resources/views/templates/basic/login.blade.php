@extends('staff.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/admin/images/login.jpg') }}')">
        <div class="container custom-container">
            <div class="row justify-content-center">
                <div class="col-xxl-5 col-xl-5 col-lg-6 col-md-8 col-sm-11">
                    <div class="login-area">
                        <div class="login-wrapper">
                            <div class="login-wrapper__top">
                                <a class="site-logo site-title" href="{{ route('home') }}"><img
                                        src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="logo"></a>
                                <h4 class="title text-white mt-3">@lang('Welcome to')
                                    <strong>{{ __(gs('site_name')) }}</strong>
                                </h4>

                            </div>
                            <div class="login-wrapper__body">
                                <form method="POST" class="cmn-form verify-gcaptcha login-form route">
                                    @csrf
                                    <div class="form-group">
                                        <label>@lang('Select Access')</label>
                                        <select name="access" id="access" class="form-select select2"
                                            data-minimum-results-for-search="-1" required>
                                            <option value="" selected disabled> @lang('Select One')</option>
                                            <option value="" data-route="{{ route('doctor.login') }}"
                                                data-href="{{ route('doctor.password.reset') }}">@lang('Doctor')
                                            </option>
                                            <option value="" data-route="{{ route('assistant.login') }}"
                                                data-href="{{ route('assistant.password.reset') }}">@lang('Assistant')
                                            </option>
                                            <option value="" data-route="{{ route('staff.login') }}"
                                                data-href="{{ route('staff.password.reset') }}">@lang('Staff')
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('Username')</label>
                                        <input type="text" class="form-control" value="{{ old('username') }}"
                                            name="username" required>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('Password')</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>

                                    <x-captcha />

                                    <div class="d-flex flex-wrap justify-content-between">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" name="remember" type="checkbox" id="remember">
                                            <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                                        </div>
                                        <a class="forget-text forget">@lang('Forgot Password?')</a>
                                    </div>
                                    <button type="submit" class="btn cmn-btn w-100">@lang('LOGIN')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .form-select {
            line-height: 2.2 !important;
            box-shadow: unset !important
        }

        .login-wrapper__top {
            padding: 34px 12px 34px 12px !important;
        }

        /* Custom styles for Select2 dropdown */
        .select2-container--default .select2-selection--single {
            background-color: #1e157d;
            color: white;
            border-color: #625ca4 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            border-left-color: white;
        }

        .select2-dropdown {
            background-color: #1e157d;
            color: white;
        }

        .select2-results__option {
            background-color: #1e157d;
            color: white;
        }

        .select2-results__option--highlighted {
            background-color: #625ca4;
            color: white;
        }

        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #291da7 !important;
            color: #fff;
        }

        .select2-results__option.select2-results__option--selected,
        .select2-results__option--selectable {
            padding: 12px 14px !important;
            border-bottom: 1px solid #291da7;
        }

        img {
            max-width: 50%;
            height: auto;
        }
    </style>
@endpush
@push('script')
    <script>
        'use strict';
        $(document).ready(function() {
            var elemData = $("select[name=access]");

            var targetRoute = elemData.find('option:selected').data('route');
            var forget = elemData.find('option:selected').data('href');
            $('.route').attr('action', targetRoute);
            $(".forget").attr("href", forget);

            $("select[name=access]").on('change', function() {
                var targetRoute = $(this).find('option:selected').data('route');
                var forget = $(this).find('option:selected').data('href');
                $('.route').attr('action', targetRoute);
                $(".forget").attr("href", forget);
            });

            $(document).on('click', '.forget', function() {
                var forgetLink = $(".forget").attr("href");
                if (!forgetLink) {
                    notify('error', 'Please Select your role first');
                }
            });



        });
    </script>
@endpush
