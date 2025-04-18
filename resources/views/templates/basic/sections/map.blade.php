@php
    $contactUsContent = getContent('contact_us.content', true);
@endphp

<!-- map-section start -->
<section class="contact-map-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 px-0">
                <div class="contact-map">
                    <iframe
                        src="https://maps.google.com/maps?q={{ @$contactUsContent->data_values->latitude }},{{ @$contactUsContent->data_values->longitude }}&hl=es;z=14&amp;output=embed"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- map-section end -->
