<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @include('partials.seo')
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">

    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/chosen.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/themify.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/animate.css') }}">
    <!-- main style css link -->
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/style.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/custom.css') }}">
    @stack('style-lib')
    @stack('style')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}">

</head>
@php echo loadExtension('google-analytics') @endphp

<body>

    @stack('fbComment')

    <div class="body-overlay"></div>

    <div class="loader">
        <div class="heartbeatloader">
            <svg class="svgdraw" width="100%" height="100%" viewBox="0 0 150 400">
                <path class="path"
                    d="M 0 200 l 40 0 l 5 -40 l 5 40 l 10 0 l 5 15 l 10 -140 l 10 220 l 5 -95 l 10 0 l 5 20 l 5 -20 l 30 0"
                    fill="transparent" stroke-width="4" stroke="black" />
            </svg>
            <div class="innercircle"></div>
            <div class="outercircle"></div>
        </div>
    </div>

    @include($activeTemplate . 'partials.header')

    <div class="scrollToTop">
        <span class="scroll-icon">
            <i class="fa fa-angle-up"></i>
        </span>
    </div>

    <div class="all-sections">
        @if (!request()->routeIs('home'))
            @include($activeTemplate . 'partials.breadcrumb')
        @endif

        @yield('content')
    </div>

    @include($activeTemplate . 'partials.footer')



    @php
        $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
    @endphp
    @if ($cookie->data_values->status == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
        <!-- cookies dark version start -->
        <div class="cookies-card text-center hide">
            <div class="cookies-card__icon bg--base">
                <i class="las la-cookie-bite"></i>
            </div>
            <p class="mt-4 cookies-card__content">{{ $cookie->data_values->short_desc }} <a
                    href="{{ route('cookie.policy') }}" target="_blank">@lang('learn more')</a></p>
            <div class="cookies-card__btn mt-4">
                <a href="javascript:void(0)" class="btn cmn-btn w-100 policy">@lang('Allow')</a>
            </div>
        </div>
        <!-- cookies dark version end -->
    @endif


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/chosen.jquery.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/swiper.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/wow.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/main.js') }}"></script>

    @stack('script-lib')

    @php echo loadExtension('tawk-chat') @endphp

    @include('partials.notify')

    @if (gs('pn'))
        @include('partials.push_script')
    @endif

    <style>
        /* Custom Dropdown Css Start */
        .custom--dropdown {
            position: relative;
            width: auto;
            min-width: 50px;
        }

        .custom--dropdown:after {
            content: "\f107";
            position: absolute;
            font-weight: 900;
            font-family: "Line Awesome Free";
            top: 50%;
            right: 10px;
            -webkit-transition: auto;
            transition: auto;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            -webkit-transition: 0.3s ease-in-out;
            transition: 0.3s ease-in-out;
            font-size: 14px;
            color: #003367;
        }

        .custom--dropdown.open:after {
            -webkit-transform: translateY(-50%) rotate(180deg);
            transform: translateY(-50%) rotate(180deg);
        }

        .custom--dropdown>.custom--dropdown__selected {
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            color: #fff;
            font-family: var(--body-fonts);
            padding-right: 30px;
        }

        .custom--dropdown>.dropdown-list {
            position: absolute;
            background-color: #fff;
            width: 100%;
            border-radius: 10px;
            -webkit-box-shadow: 0px 12px 24px rgba(21, 18, 51, 0.13);
            box-shadow: 0px 12px 24px rgba(21, 18, 51, 0.13);
            opacity: 0;
            overflow: hidden;
            -webkit-transition: 0.25s ease-in-out;
            transition: 0.25s ease-in-out;
            -webkit-transform: scaleY(0);
            transform: scaleY(0);
            -webkit-transform-origin: top center;
            transform-origin: top center;
            top: 100%;
            margin-top: 5px;
            z-index: -1;
            visibility: hidden;
            max-height: 230px;
            min-width: 100px;
            overflow-y: auto !important;
        }

        .custom--dropdown>.dropdown-list::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom--dropdown>.dropdown-list::-webkit-scrollbar-thumb {
            background-color: hsl(var(--base));
        }

        .custom--dropdown>.dropdown-list::-webkit-scrollbar-thumb {
            background-color: hsl(var(--base));
        }

        .custom--dropdown.open>.dropdown-list {
            -webkit-transform: scale(1);
            transform: scale(1);
            opacity: 1;
            visibility: visible;
            z-index: 999 !important;
        }

        .dropdown-list>.dropdown-list__item {
            padding: 6px 15px;
            cursor: pointer;
            -webkit-transition: 0.3s;
            transition: 0.3s;
            font-size: 14px;
        }

        .dropdown-list>.dropdown-list__item:hover {
            background-color: hsl(var(--base)/0.08);
        }

        .dropdown-list>.dropdown-list__item,
        .custom--dropdown>.custom--dropdown__selected {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
        }

        .dropdown-list>.dropdown-list__item .thumb,
        .custom--dropdown>.custom--dropdown__selected .thumb {
            max-width: 30px;
        }

        .dropdown-list>.dropdown-list__item .text,
        .custom--dropdown>.custom--dropdown__selected .text {
            width: calc(100% - 30px);
            padding-left: 10px;
            color: #003367;
            font-weight: 500;
        }

        .dropdown-list>.dropdown-list__item.selected,
        .dropdown-list>.dropdown-list__item.selected:hover {
            background-color: hsl(var(--base)/0.15);
        }

        /* Custom Dropdown Css End */
    </style>

    @stack('script')

    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });

            $('.policy').on('click', function() {
                $.get('{{ route('cookie.accept') }}', function(response) {
                    $('.cookies-card').addClass('d-none');
                });
            });

            setTimeout(function() {
                $('.cookies-card').removeClass('hide')
            }, 2000);

            function formatState(state) {
                if (!state.id) return state.text;
                let gatewayData = $(state.element).data();
                return $(
                    `<div class="d-flex gap-2">${gatewayData.imageSrc ? `<div class="select2-image-wrapper"><img class="select2-image" src="${gatewayData.imageSrc}"></div>` : '' }<div class="select2-content"> <p class="select2-title">${gatewayData.title}</p><p class="select2-subtitle">${gatewayData.subtitle}</p></div></div>`
                    );
            }

            $('.select2').each(function(index, element) {
                $(element).select2();
            });


            $('.select2-basic').each(function(index, element) {
                $(element).select2({
                    dropdownParent: $(element).closest('.select2-parent')
                });
            });


            var inputElements = $('[type=text],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }

            });

            Array.from(document.querySelectorAll('table')).forEach(table => {
                let heading = table.querySelectorAll('thead tr th');
                Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
                    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
                        colum.setAttribute('data-label', heading[i].innerText)
                    });
                });
            });



            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });

        })(jQuery);
    </script>

</body>

</html>
