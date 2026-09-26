<?php

use App\Http\Controllers\MobileRechargeController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SimOfferController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

  Route::post('/send-otp', [OtpController::class, 'sendOtpUser'])
    ->middleware('throttle:send-otp');

  Route::post('/verify-otp', [OtpController::class, 'verifyOtpUser'])
    ->middleware('throttle:verify-otp');

  Route::post('/pin-login', [UserController::class, 'pinLogin'])
    ->middleware('throttle:pin-login');

  Route::post('/refresh-token', [UserController::class, 'refreshToken'])
    ->middleware(middleware: 'throttle:refresh-token');

  Route::post('/register', [UserController::class, 'registerWithPin'])
    ->middleware('throttle:register');


  // Route::post('/test', (Request $request) => {


  // $rolePermissions = $user->role->permissions;

  // $overrides = $user->permissionOverrides()->pluck('is_active', 'permission_id')->toArray();

  // $permissions = $rolePermissions->map(function($p) use ($overrides) {
  //     if(isset($overrides[$p->id])) {
  //         $p->is_active = $overrides[$p->id];
  //     } else {
  //         $p->is_active = true;
  //     }
  //     return $p;
  // })->filter(fn($p) => $p->is_active);


  // });


  Route::post('/test', [TransactionController::class, 'lastFive']);

});

Route::prefix('user')->middleware(['auth.user'])->group(function () {

  Route::get('/profile', [UserController::class, 'profile']);
  Route::get('/balance', [UserController::class, 'getBalance']);
  Route::post('/logout', [UserController::class, 'logout']);

  // Additional authenticated user routes can be added here
  Route::get('/recharge-info', [MobileRechargeController::class, 'getRechargeInfo']);
  Route::get('/recharge-operators', [MobileRechargeController::class, 'getRechargeOperators']);

  // get and post
  Route::match(['get', 'post'], '/sim-offers', [SimOfferController::class, 'allOffers'])
    ->middleware('user.permission:sim_offer');

  // Transaction routes
  Route::match(['get', 'post'], '/transactions', [TransactionController::class, 'transactions']);
  Route::get('/transactions/last-five', [TransactionController::class, 'lastFive']);

  Route::post('/recharge', [MobileRechargeController::class, 'recharge'])
    ->middleware('user.trx.limit:mobile_recharge');

  Route::post('/offer-purchase', [SimOfferController::class, 'offerPurchase'])
    ->middleware('user.trx.limit:offer_purchase');


});

// sim offer routes
Route::prefix('user')->middleware(['auth.user'])->group(function () {

  // get
  Route::get('/sim-offers', [SimOfferController::class, 'allOffers'])
    ->middleware('user.permission:sim_offer');


});

// add money routes
Route::prefix('user')->middleware(['auth.user'])->group(function () {

  // get
  Route::get('/payment-info', [PaymentController::class, 'getPaymentInfo'])
    ->middleware('user.permission:add_money');


  //   
  Route::post('/create-payment', [PaymentController::class, 'createPayment']);



});