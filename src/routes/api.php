<?php


use App\Models\User;
use CQN\NAuthModule\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

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

Route::get('403', function () {
    return response()->json([
        'success' => false,
        'message' => 'You do not have the required authorization.'
    ]);
})->name('403');

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('/login/google', [AuthController::class, 'googleLogin']);
    Route::get('/login/google/callback/demo', function () {
    });
    Route::get('/login/google/callback', [AuthController::class, 'googleLoginCallback']);
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('token', function () {
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        });
    });
});
