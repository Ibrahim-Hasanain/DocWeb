<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});


// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::controller('SiteController')->group(function () {
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');

    Route::post('/subscribe', 'subscribe')->name('subscribe');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('blogs', 'blogs')->name('blogs');

    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('login', 'login')->name('login');

    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});

Route::controller('DoctorAppointmentController')->prefix('doctors')->name('doctors.')->group(function () {
    Route::get('all', 'doctors')->name('all');
    Route::get('search', 'doctors')->name('search');

    Route::get('locations/{location}', 'locations')->name('locations');
    Route::get('departments/{department}', 'departments')->name('departments');
    Route::get('featured', 'featured')->name('featured');

    //Booking
    Route::post('appointment/store/{id}', 'store')->name('appointment.store');
    Route::get('booking/{id?}', 'booking')->name('booking');

    Route::get('booking/date/availability', 'availability')->name('appointment.available.date');
});

// Payment
Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
    Route::post('insert', 'depositInsert')->name('insert');
    Route::get('confirm', 'depositConfirm')->name('confirm');
    Route::get('manual', 'manualDepositConfirm')->name('manual.confirm');
    Route::post('manual', 'manualDepositUpdate')->name('manual.update');
    Route::any('/{encryptedAppointmentId}', 'deposit')->name('index');
});
