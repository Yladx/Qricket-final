<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventTicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventTicketShareController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProfanityFilterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Discount validation API
Route::post('/discount/validate', [DiscountController::class, 'validateDiscount']);

// Profanity filter API (public, but rate-limited)
Route::post('/profanity/filter', [ProfanityFilterController::class, 'filter']);

// Public API routes for events
Route::get('/events/{event}/ticket-types', [EventTicketController::class, 'getTicketTypes']);
// Authenticated API routes (using web middleware for session auth)
Route::middleware(['web', 'auth'])->group(function () {
    // User search API
    Route::get('/users/search', [UserController::class, 'search']);
    
    // Ticket sharing API
    Route::post('/tickets/share', [EventTicketShareController::class, 'share']);
    Route::post('/tickets/share/{id}/accept', [EventTicketShareController::class, 'accept']);
    Route::post('/tickets/share/{id}/decline', [EventTicketShareController::class, 'decline']);
    Route::get('/tickets/shares/received', [EventTicketShareController::class, 'getReceivedShares']);
    Route::post('/tickets/share/log', [EventTicketShareController::class, 'logSharing']);
});

