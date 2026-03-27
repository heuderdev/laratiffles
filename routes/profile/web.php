<?php
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
