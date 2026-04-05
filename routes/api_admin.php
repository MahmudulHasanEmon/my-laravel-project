<?php

use App\Http\Controllers\OtpController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserManagementController;

Route::prefix('admin')->group(function () {

  Route::post('/send-otp', [OtpController::class, 'sendOtpAdmin'])
    ->middleware('throttle:send-otp');

  Route::post('/verify-otp', [OtpController::class, 'verifyOtpAdmin'])
    ->middleware('throttle:verify-otp');
  
  Route::post('/pin-login', [AdminController::class, 'pinLogin'])
    ->middleware('throttle:pin-login');
  
  Route::post('/refresh-token', [AdminController::class, 'refreshToken'])
    ->middleware( 'throttle:refresh-token');

  Route::get('/users-roles-permissions', [UserManagementController::class, 'userRolesPermissions']);

});

Route::prefix('admin')->middleware('auth.admin')->group(function () {

  Route::post('/register-admin', [AdminController::class, 'register'])
    ->middleware(['admin.permission:create_admin']);
    
  Route::get('/profile', [AdminController::class, 'profile']);
  Route::post('/logout', [AdminController::class, 'logout']);

  // User Management
  Route::get('/users', [UserManagementController::class, 'index'])
    ->middleware(['admin.permission:view_users']);

  // Route::get('/users/roles-permissions', [UserManagementController::class, 'userRolesPermissions'])
  //   ->middleware(['admin.permission:role_permission_management']);


});


