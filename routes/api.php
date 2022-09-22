<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\StateController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register',RegisterController::class);
Route::post('/login',LoginController::class);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/get-coutries',CountryController::class);


// ...

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-countries', CountryController::class);
    //Route::get('/get-states', StateController::class);

    Route::prefix('restaurants')->group(function () {
        Route::get('/list', [RestaurantController::class, 'list']);
        Route::post('/add', [RestaurantController::class, 'store']);
        Route::post('/update/{restaurantId}', [RestaurantController::class, 'update']);
        Route::delete('/archive/{restaurantId}', [RestaurantController::class, 'archive']);
    });
});


// ...

Route::middleware('auth:sanctum')->group(function () {
    // ...

    Route::middleware('restaurant_required')->group(function () {
        Route::prefix('menu-items')->group(function () {
            Route::get('/list', [MenuItemController::class, 'list']);
            Route::post('/add', [MenuItemController::class, 'store']);
            Route::post('/update/{menuItemId}', [MenuItemController::class, 'update'])->name('update_menu_item');
            Route::delete('/archive/{menuItemId}', [MenuItemController::class, 'archive']);

        });

// ...
            Route::post('/mark-as-non-operational/{tableId}', [TableStatusController::class, 'markAsNonOperational']);
            Route::post('/mark-as-available/{tableId}', [TableStatusController::class, 'markAsAvailable']);
            Route::post('/mark-as-reserved/{tableId}', [TableStatusController::class, 'markAsReserved']);

            Route::prefix('orders')->group(function () {
                Route::post('/book-a-table/{tableId}', [OrderController::class, 'bookATable']);
                Route::get('/details/{orderId}', [OrderController::class, 'details']);
                Route::get('/list-open', [OrderController::class, 'listOpen']);
                Route::get('/list-completed', [OrderController::class, 'listCompleted']);

            });
    });
});
