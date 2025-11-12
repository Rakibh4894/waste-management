<?php

use App\Http\Controllers\CityCorporationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserConfigController;
use App\Http\Controllers\WasteRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home-page');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    Route::post('/toggle-dark-mode', [UserConfigController::class, 'toggleDarkMode']);
    Route::post('/fullScreen-mode', [UserConfigController::class, 'fullScreenMode']);

    //------------------------------Role route start------------------------------------
    Route::get('roles/admin', [RoleController::class, 'index']);
    Route::get('roles/list', [RoleController::class, 'getList']);
    Route::get('roles/add', [RoleController::class, 'create']);
    Route::post('roles/store', [RoleController::class, 'store']);
    Route::get('roles/edit/{id}', [RoleController::class, 'edit']);
    Route::post('roles/permission-create', [RoleController::class, 'createRole']);
    Route::post('roles/permission-update', [RoleController::class, 'updateRole']);



    Route::get('roles/assign-permission', [RoleController::class, 'assignPermission']);
    Route::get('roles/get-permissions-list/', [RoleController::class, 'getPermissionsList']);
    Route::get('users/get-users-for-permission', [UserController::class, 'getUsersForPermission']);
    Route::post('roles/assign-permission-to-role/{id}', [RoleController::class, 'assignPermissionToRole']);
    Route::post('roles/revoke-permission-from-role/{id}', [RoleController::class, 'revokePermissionFromRole']);
    Route::post('roles/assign-batch-permission-to-role', [RoleController::class, 'assignBatchPermissions']);
    Route::post('roles/revoke-batch-permission-to-role', [RoleController::class, 'revokeBatchPermissions']);
    Route::post('roles/assign-revoke-multiple-permission', [RoleController::class, 'assignRevokeMultiplePermission']);
    // -- new-----
    Route::get('roles/permission-assign/{id}', [RoleController::class, 'assign']);

    Route::post('roles/update/{id}', [RoleController::class, 'update']);
    Route::get('roles/delete/{id}', [RoleController::class, 'destroy']);
    //------------------------------Role route end------------------------------------

    //----------------------------Permission Route start----------------------------------
    Route::get('permissions/admin', [PermissionController::class, 'index']);
    Route::post('permissions/getMenu', [PermissionController::class, 'getMenu']);
    Route::get('permissions/getMenu/{id}', [PermissionController::class, 'getMenubyId']);
    Route::post('permissions/getSubMenu', [PermissionController::class, 'getSubMenu']);
    Route::get('permissions/getSubMenu/{id}', [PermissionController::class, 'getSubMenuById']);
    Route::post('permissions/getSubMenuPermission', [PermissionController::class, 'getSubMenuPermission']);

    Route::get('permissions/list', [PermissionController::class, 'getList']);
    // Route::get('permissions/add', [PermissionController::class, 'create']);
    Route::post('permissions/store', [PermissionController::class, 'store']);
    Route::get('permissions/edit/{id}', [PermissionController::class, 'edit']);
    Route::post('permissions/update', [PermissionController::class, 'update']);
    Route::get('permissions/delete/{id}', [PermissionController::class, 'destroy']);

    Route::get('permissions/generate', [PermissionController::class, 'generatePermission']);
    Route::get('permissions/get-urls-list', [PermissionController::class, 'getUrlsList']);

    Route::get('permissions/permission-generation', [PermissionController::class, 'permissionGeneration'])->name('admins.permissions.permission-generation');
    Route::post('permissions/bulk-store', [PermissionController::class, 'storeGenerated'])->name('admins.permissions.bulk-store');

    //------------------------------permission route end--------------------------------

    //    -------------------------------Manage Users start-----------------------------------

    Route::get('users/manage-users', [UserController::class, 'manageUser']);
    Route::post('users/manage-users/store', [UserController::class, 'storeManageUser']);
    Route::get('users/manage-users/edit/{id}', [UserController::class, 'manageUsersEdit']);
    Route::post('users/manage-users/update', [UserController::class, 'manageUserUpdate']);
    Route::post('users/manage-users/delete/{id}', [UserController::class, 'manageUsersDestroy']);
    Route::get('users/manage-users-permission', [UserController::class, 'manageUserPermission']);
    Route::get('users/get-users-for-permission', [UserController::class, 'getUsersForPermission']);
    Route::get('users/assign-revoke-permission/{id}', [UserController::class, 'assignRevokePermission']);
    Route::post('users/get-user-permissions-list', [UserController::class, 'getUserPermissionsList']);
    Route::post('users/assign-permission/{id}', [UserController::class, 'assignPermissionToUser']);
    Route::post('users/revoke-permission/{id}', [UserController::class, 'revokePermissionFromUser']);
    Route::get('users/change-password', [UserController::class, 'changePassword'])->name('change.password');
    Route::post('users/change-password/store', [UserController::class, 'storeChangePassword'])->name('update.password');
//    -------------------------------Manage Users end-------------------------------------

    Route::get('wards', [WardController::class, 'index']);
    Route::post('wards/store', [WardController::class, 'store']);
    Route::get('wards/edit/{id}', [WardController::class, 'edit']);
    Route::post('wards/update/{id}', [WardController::class, 'update']);
    Route::post('wards/delete/{id}', [WardController::class, 'destroy']);

    Route::get('city-corporations', [CityCorporationController::class, 'index']);
    Route::post('city-corporations/store', [CityCorporationController::class, 'store']);
    Route::get('city-corporations/edit/{id}', [CityCorporationController::class, 'edit']);
    Route::post('city-corporations/update/{id}', [CityCorporationController::class, 'update']);
    Route::post('city-corporations/delete/{id}', [CityCorporationController::class, 'destroy']);

    // Route::resource('waste-requests', WasteRequestController::class);
    // Route::post('waste-requests/assign/{id}', [WasteRequestController::class, 'assign'])->name('waste-requests.assign');



    // Waste Request Management
Route::prefix('waste-requests')->group(function () {

    // List + AJAX DataTable
    Route::get('/', [WasteRequestController::class, 'index'])->name('waste-requests.index');

    // Create form
    Route::get('/create', [WasteRequestController::class, 'create'])->name('waste-requests.create');

    // Store new request
    Route::post('/', [WasteRequestController::class, 'store'])->name('waste-requests.store');

    // Show single request (details page)
    Route::get('/{waste_request}', [WasteRequestController::class, 'show'])->name('waste-requests.show');

    // Assign collector to a request
    Route::post('/{id}/assign', [WasteRequestController::class, 'assign'])->name('waste-requests.assign');
});

    Route::get('search/employeeByNameOrID', [EmployeeController::class, 'searchEmployeeByNameOrID']);
    Route::get('/getEmployeeByNameOrID/empId/{empId}', [EmployeeController::class, 'getEmployeeByNameOrID']);
    

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
