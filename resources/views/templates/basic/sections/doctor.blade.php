@php
    $doctorContent = getContent('doctor.content', true);
    $doctors    = \App\Models\Doctor::active()
        ->with('department', 'location')
        ->orderBy('id', 'DESC')
        ->get(['id', 'name', 'qualification', 'mobile', 'image', 'department_id', 'location_id']);
@endphp
<!-- our doctor section start -->
<section class="booking-section ptb-80">
    <div class="container-fluid">
        <div class="row ml-b-20">
            <div class="booking-right-area">
                <div class="col-lg-12">
                    <div class="section-header">
                        <h2 class="section-title">{{ __($doctorContent->data_values->heading) }}</h2>
                        <p class="m-0">{{ __($doctorContent->data_values->subheading) }}</p>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="booking-slider">
                        <div class="swiper-wrapper">
                            @foreach ($doctors as $doctor)
                                <div class="swiper-slide">
                                    <div class="booking-item">
                                        <div class="booking-thumb">
                                            <img src="{{ getImage(getFilePath('doctorProfile') . '/' . @$doctor->image, getFileSize('doctorProfile')) }}"
                                                alt="@lang('doctor')">
                                            <div class="doc-deg">{{ __($doctor->department->name) }}</div>
                                            @if ($doctor->featured)
                                                <span class="fav-btn"><i class="las la-medal"></i></span>
                                            @endif
                                        </div>
                                        <div class="booking-content">
                                            <span class="sub-title"><a
                                                    href="{{ route('doctors.departments', $doctor->department->id) }}">{{ __($doctor->department->name) }}</a></span>
                                            <h5 class="title">{{ __($doctor->name) }} <i
                                                    class="fas fa-check-circle"></i></h5>
                                            <p>{{ strLimit(__($doctor->qualification), 50) }}</p>
                                            <ul class="booking-list">
                                                <li><i class="las la-street-view"></i><a
                                                        href="{{ route('doctors.locations', $doctor->location->id) }}">{{ __($doctor->location->name) }}</a>
                                                </li>
                                                <li><i class="la la-phone"></i> {{ __($doctor->mobile) }}</li>
                                            </ul>
                                            <div class="booking-btn">
                                                <a href="{{ route('doctors.booking',$doctor->id) }}" class="cmn-btn w-100 text-center">@lang('Book Now')</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="ruddra-next">
                            <i class="las la-angle-right"></i>
                        </div>
                        <div class="ruddra-prev">
                            <i class="las la-angle-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- booking-section end -->
