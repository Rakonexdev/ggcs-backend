<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GGCS API Routes
|--------------------------------------------------------------------------
*/

// Auth (public)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Users (Super Admin)
    Route::apiResource('users', UserController::class)->except('destroy');
    Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus']);

    // Roles & Permissions (Super Admin)
    Route::get('/roles', [RolePermissionController::class, 'roles']);
    Route::get('/permissions', [RolePermissionController::class, 'permissions']);
    Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'updateRolePermissions']);
    Route::get('/settings/menus', [RolePermissionController::class, 'getMenus']);
    Route::put('/settings/menus', [RolePermissionController::class, 'updateMenus']);

    // Companies
    Route::apiResource('companies', CompanyController::class)->only(['index', 'store', 'show']);

    // Persons
    Route::apiResource('persons', PersonController::class)->except('destroy');

    // Projects
    Route::apiResource('projects', ProjectController::class)->except('destroy');
    Route::patch('/projects/{project}/status', [ProjectController::class, 'updateStatus']);
    Route::post('/projects/{project}/professions', [ProjectController::class, 'storeProfession']);
    Route::put('/projects/{project}/professions/{profession}', [ProjectController::class, 'updateProfession']);
    Route::delete('/projects/{project}/professions/{profession}', [ProjectController::class, 'destroyProfession']);

    // Timesheets
    Route::apiResource('timesheets', TimesheetController::class);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show']);

    // Collections
    Route::apiResource('collections', CollectionController::class)->only(['index', 'store', 'show']);
    Route::patch('/collections/{collection}/verify', [CollectionController::class, 'verify']);

    // Expense Categories
    Route::get('/expense-categories', [ExpenseController::class, 'categories']);
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory']);
    Route::put('/expense-categories/{category}', [ExpenseController::class, 'updateCategory']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class)->only(['index', 'store', 'show']);

    // Reports
    Route::get('/reports/dashboard-stats', [ReportController::class, 'dashboardStats']);
    Route::get('/reports/outstanding-invoices', [ReportController::class, 'outstandingInvoices']);
    Route::get('/reports/collections-summary', [ReportController::class, 'collectionsSummary']);
    Route::get('/reports/collections-feed', [ReportController::class, 'collectionsFeed']);
    Route::get('/reports/expenses-by-category', [ReportController::class, 'expensesByCategory']);
    Route::get('/audit-logs', [ReportController::class, 'auditLogs']);

    // Uploads
    Route::post('/uploads/id-photo', [UploadController::class, 'idPhoto']);
    Route::post('/uploads/lpo', [UploadController::class, 'lpo']);
    Route::post('/uploads/invoice-copy', [UploadController::class, 'invoiceCopy']);
    Route::post('/uploads/expense-document', [UploadController::class, 'expenseDocument']);
});
