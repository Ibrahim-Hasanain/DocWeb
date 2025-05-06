<?php
header("Content-Type:text/css");
function checkhexcolor($color)
{
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
}

if (isset($_GET['color']) and $_GET['color'] != '') {
    $color = "#" . $_GET['color'];
}

if (!$color or !checkhexcolor($color)) {
    $color = "#336699";
}
?>

*::-webkit-scrollbar-button, *::-webkit-scrollbar-thumb, .scrollToTop, .cmn-btn-active:focus, .cmn-btn-active:hover, .cmn-btn, .border-btn.active, .border-btn:hover, .small-btn::after, .nice-select, .header-bottom-area .navbar-collapse .main-menu li a::after, .header-bottom-area .navbar-collapse .main-menu li .sub-menu li::before, .header-search-form .header-search-btn, .title-border::before, .title-border::after, .title-border-left::after, .banner-slider .banner-pagination span.swiper-pagination-bullet-active::after, .ruddra-next:hover, .ruddra-prev:hover, .choose-slider .swiper-pagination .swiper-pagination-bullet-active, .booking-item .booking-thumb .doc-deg, .booking-item .booking-thumb .fav-btn:hover, .booking-section-two .booking-tag li:hover a, .overview-booking-list .booked::before, .overview-booking-area .clearfix li a.disabled, .faq-wrapper .faq-item.open .right-icon::before, .faq-wrapper .faq-item.open .right-icon::after, .contact-form-area .contact-form .submit-btn, .contact-item-icon i, .blog-details-section .tag-item-wrapper .tag-item:hover, .comments-section .comment-item .comment-content:hover .reply-button i, .footer-form .submit-btn, .date-select, .date-select:focus, .bg-site-color, .card-header, .submit-button, .innercircle, ::selection, .banner-slider .swiper-pagination .swiper-pagination-bullet. .blog-item .blog-thumb .blog-cat {
background-color: <?php echo $color ?>;
}

.pagination .page-item.active .page-link, .pagination .page-item:hover .page-link, .client-content::before, .file-upload-wrapper:before, .pagination .page-item.disabled span {
background: <?php echo $color ?>;
}

.cmn-btn {
background-color: <?php echo $color ?>;
}

.innercircle{
background-color: <?php echo $color ?>;
}

.header-bottom-area .navbar-collapse .main-menu li a::after {
background-color: <?php echo $color ?>;
}

.admin-reply-section{
background-color: <?php echo $color ?>29;
}

.outercircle{
background-color: <?php echo $color ?>90;
}

.footer-form .submit-btn {
background-color: <?php echo $color ?>;
}

.booking-item .booking-thumb .doc-deg{
background-color: <?php echo $color ?>;
}

.border-btn.active{
background-color: <?php echo $color ?>;
}
.scrollToTop{
background-color: <?php echo $color ?>;
}
.copied::after{
background-color: <?php echo $color ?>;
}

.select2-container--default .select2-search--dropdown .select2-search__field:focus {
border-color: <?php echo $color ?> !important;
}

.select2-container--open .select2-selection.select2-selection--single,
.select2-container--open .select2-selection.select2-selection--multiple {
border-color: <?php echo $color ?> !important;
}

.payment-card-title {
background-color: <?php echo $color ?> !important;
}

.contact-form-area .contact-form .submit-btn {
background-color: <?php echo $color ?> !important;
}

.card-header {
background-color: <?php echo $color ?> !important;
}

.blog-details-section .single-popular-item .popular-item-content .title:hover{
color: <?php echo $color ?>;
}

.contact-item-icon i {
background-color: <?php echo $color ?> !important;
}

.post-share li a {
color: <?php echo $color ?>;
border: 1px solid <?php echo $color ?>;
}

.post-share li a:hover{
background-color: <?php echo $color ?> !important;
border: 1px solid <?php echo $color ?>;
}

.payment-system-list {
--hover-border-color: <?php echo $color ?> !important;
}


.payment-system-list.is-scrollable::-webkit-scrollbar-thumb {
background-color: <?php echo $color ?> !important;
}

.select2-selection--single {
background: <?php echo $color ?> !important;
}


.payment-item:has(.payment-item__radio:checked) .payment-item__check {
border: 3px solid <?php echo $color ?> !important;
}

.payment-item__check {
border: 1px solid <?php echo $color ?> !important;
}

.blog-item .blog-thumb .blog-cat{
background-color: <?php echo $color ?>;
}
.choose-slider .swiper-pagination .swiper-pagination-bullet-active{
background-color: <?php echo $color ?>90;
}

.banner-slider .swiper-pagination .swiper-pagination-bullet {
background-color: <?php echo $color ?>;
}

.scrollToTop:hover, .cmn-btn-active, .custom-btn:hover, .header-bottom-area .navbar-collapse .main-menu li.active a, .header-bottom-area .navbar-collapse .main-menu li:hover a, .language-select .nice-select span, .navbar-toggler span, .header-form .header-form-area button, .header-form .header-form-area input[type="button"], .header-form .header-form-area input[type="reset"], .header-form .header-form-area input[type="submit"], .breadcrumb li, .breadcrumb-item.active::before, .banner-slider .banner-pagination span.swiper-pagination-bullet-active, .ruddra-next, .ruddra-prev, .team-content .team-list li i, .client-content .client-icon i, .blog-item .blog-content .title:hover, .call-to-action-area .call-info .call-info-content .title a, .call-to-action-area .mail-info .mail-info-content .title a, .booking-item .booking-content .sub-title, .booking-item .booking-content .booking-list li span, .overview-tab-wrapper .tab-menu li.active, .booking-confirm-area .booking-confirm-list li span, .blog-details-section .category-content li:hover, .privacy-area p a, .ticket-button, .close-button, .text-color, .footer-social li a i:hover, .footer-contact li i, .footer-menus li a i {
color: <?php echo $color ?>;
}

.footer-social li, .footer-social li a:hover, .footer-social li a.active, .cmn--text {
color: <?php echo $color ?> !important;
}

.scrollToTop, .cmn-btn-active, .title-border-left::before, .ruddra-next, .ruddra-prev, .border-btn {
border: 1px solid <?php echo $color ?>;
}


.overview-content .overview-list li .overview-user .before-circle {
border: 2px solid <?php echo $color ?>;
}

.cmn-btn-active:focus, .cmn-btn-active:hover, .cmn-btn:focus, .cmn-btn:hover {
box-shadow: 0px 15px 20px -8px <?php echo $color ?>;
}

.header-bottom-area .navbar-collapse .main-menu li .sub-menu {
border-left: 3px solid <?php echo $color ?>;
}

.search-bar a {
border: 2px dashed <?php echo $color ?>;
}

.language-select .nice-select:after {
border-color: <?php echo $color ?> transparent transparent;
}

.header-form .header-form-area input {
border-bottom: 1px solid <?php echo $color ?>;
}

.overview-tab-wrapper .tab-menu li.active {
border-bottom: 2px solid <?php echo $color ?>;
}

.pagination .page-item.active .page-link, .pagination .page-item:hover .page-link, .date-select, .date-select:focus {
border-color: <?php echo $color ?>;
}

.payment-item .payment-badge{
border-right: 60px solid <?php echo $color ?>;
}