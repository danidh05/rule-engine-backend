<?php

use App\Http\Controllers\Api\RuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Rules Management API Routes
|--------------------------------------------------------------------------
|
| Here we define the API routes for managing promotion rules including
| CRUD operations and rule evaluation endpoints.
|
*/

// Rules CRUD operations
Route::apiResource('rules', RuleController::class);

// Additional rule operations
Route::patch('rules/{rule}/toggle-status', [RuleController::class, 'toggleStatus'])
    ->name('api.rules.toggle-status');

// Rule evaluation endpoint
Route::post('evaluate', [RuleController::class, 'evaluate'])
    ->name('api.evaluate');

// Health check endpoint
Route::get('health', [RuleController::class, 'health'])
    ->name('api.health');

/*
|--------------------------------------------------------------------------
| API Documentation Routes
|--------------------------------------------------------------------------
|
| Routes for API documentation and testing endpoints.
|
*/

// API information endpoint
Route::get('info', function () {
    return response()->json([
        'name' => 'Promotion Engine API',
        'version' => '1.0.0',
        'description' => 'B2B eCommerce promotion rules engine API',
        'endpoints' => [
            'rules' => [
                'GET /api/rules' => 'List all rules',
                'POST /api/rules' => 'Create a new rule',
                'GET /api/rules/{id}' => 'Get a specific rule',
                'PUT /api/rules/{id}' => 'Update a rule',
                'DELETE /api/rules/{id}' => 'Delete a rule',
                'PATCH /api/rules/{id}/toggle-status' => 'Toggle rule active status',
            ],
            'evaluation' => [
                'POST /api/evaluate' => 'Evaluate rules against line and customer data',
            ],
            'system' => [
                'GET /api/health' => 'Health check endpoint',
                'GET /api/info' => 'API information',
            ],
        ],
        'documentation' => url('/docs/api'),
        'timestamp' => now()->toISOString(),
    ]);
})->name('api.info');