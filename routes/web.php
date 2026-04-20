<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail', function () {
    Mail::raw('Test SocialGames', function ($message) {
        $message->to('tonmail@gmail.com')
            ->subject('Test Resend');
    });

    return 'OK';
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect(rtrim(env('FRONTEND_URL'), '/') . '/email-verified');
})->middleware(['auth', 'signed'])->name('verification.verify');
