<?php

use App\Http\Controllers\Admin\CandidateProfileController;
use App\Http\Controllers\Admin\GroupAdminAccountController;
use App\Http\Controllers\Admin\GroupManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserAccountController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserAccountController::class, 'create'])->name('users.create');
    Route::post('/users', [UserAccountController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserAccountController::class, 'show'])->whereNumber('user')->name('users.show');
    Route::get('/users/{user}/edit', [UserAccountController::class, 'edit'])->whereNumber('user')->name('users.edit');
    Route::put('/users/{user}', [UserAccountController::class, 'update'])->whereNumber('user')->name('users.update');
    Route::post('/users/{user}/suspend', [UserAccountController::class, 'suspend'])->whereNumber('user')->name('users.suspend');
    Route::post('/users/{user}/reactivate', [UserAccountController::class, 'reactivate'])->whereNumber('user')->name('users.reactivate');
    Route::put('/users/{user}/password', [UserAccountController::class, 'resetPassword'])->whereNumber('user')->name('users.password');
    Route::put('/users/{user}/role', [UserAccountController::class, 'updateRole'])->whereNumber('user')->name('users.role');

    Route::get('/group-admins', [GroupAdminAccountController::class, 'index'])->name('group-admins.index');
    Route::get('/group-admins/create', [GroupAdminAccountController::class, 'create'])->name('group-admins.create');
    Route::post('/group-admins', [GroupAdminAccountController::class, 'store'])->name('group-admins.store');
    Route::get('/group-admins/{groupAdmin}', [GroupAdminAccountController::class, 'show'])->whereNumber('groupAdmin')->name('group-admins.show');
    Route::get('/group-admins/{groupAdmin}/edit', [GroupAdminAccountController::class, 'edit'])->whereNumber('groupAdmin')->name('group-admins.edit');
    Route::put('/group-admins/{groupAdmin}', [GroupAdminAccountController::class, 'update'])->whereNumber('groupAdmin')->name('group-admins.update');
    Route::post('/group-admins/{groupAdmin}/approve', [GroupAdminAccountController::class, 'approve'])->whereNumber('groupAdmin')->name('group-admins.approve');
    Route::post('/group-admins/{groupAdmin}/groups', [GroupAdminAccountController::class, 'addGroup'])->whereNumber('groupAdmin')->name('group-admins.groups.store');
    Route::delete('/group-admins/{groupAdmin}/groups/{group}', [GroupAdminAccountController::class, 'removeGroup'])->whereNumber(['groupAdmin', 'group'])->name('group-admins.groups.destroy');

    Route::get('/candidates', [CandidateProfileController::class, 'index'])->name('candidates.index');
    Route::get('/candidates/{candidate}', [CandidateProfileController::class, 'show'])->whereNumber('candidate')->name('candidates.show');
    Route::get('/candidates/{candidate}/edit', [CandidateProfileController::class, 'edit'])->whereNumber('candidate')->name('candidates.edit');
    Route::put('/candidates/{candidate}', [CandidateProfileController::class, 'update'])->whereNumber('candidate')->name('candidates.update');
    Route::post('/candidates/{candidate}/approve', [CandidateProfileController::class, 'approve'])->whereNumber('candidate')->name('candidates.approve');
    Route::post('/candidates/{candidate}/reject', [CandidateProfileController::class, 'reject'])->whereNumber('candidate')->name('candidates.reject');
    Route::post('/candidates/{candidate}/suspend', [CandidateProfileController::class, 'suspend'])->whereNumber('candidate')->name('candidates.suspend');
    Route::post('/candidates/{candidate}/reactivate', [CandidateProfileController::class, 'reactivate'])->whereNumber('candidate')->name('candidates.reactivate');
    Route::delete('/candidates/{candidate}', [CandidateProfileController::class, 'destroy'])->whereNumber('candidate')->name('candidates.destroy');
    Route::post('/candidates/{candidate}/photos/{photo}/approve', [CandidateProfileController::class, 'approvePhoto'])->whereNumber(['candidate', 'photo'])->name('candidates.photos.approve');
    Route::post('/candidates/{candidate}/groups', [CandidateProfileController::class, 'addGroup'])->whereNumber('candidate')->name('candidates.groups.store');
    Route::delete('/candidates/{candidate}/groups/{group}', [CandidateProfileController::class, 'removeGroup'])->whereNumber(['candidate', 'group'])->name('candidates.groups.destroy');

    Route::get('/groups', [GroupManagementController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupManagementController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupManagementController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupManagementController::class, 'show'])->whereNumber('group')->name('groups.show');
    Route::get('/groups/{group}/edit', [GroupManagementController::class, 'edit'])->whereNumber('group')->name('groups.edit');
    Route::put('/groups/{group}', [GroupManagementController::class, 'update'])->whereNumber('group')->name('groups.update');
    Route::post('/groups/{group}/activate', [GroupManagementController::class, 'activate'])->whereNumber('group')->name('groups.activate');
    Route::post('/groups/{group}/deactivate', [GroupManagementController::class, 'deactivate'])->whereNumber('group')->name('groups.deactivate');
    Route::delete('/groups/{group}', [GroupManagementController::class, 'destroy'])->whereNumber('group')->name('groups.destroy');
    Route::post('/groups/{group}/admins', [GroupManagementController::class, 'addAdmin'])->whereNumber('group')->name('groups.admins.store');
    Route::delete('/groups/{group}/admins/{groupAdmin}', [GroupManagementController::class, 'removeAdmin'])->whereNumber(['group', 'groupAdmin'])->name('groups.admins.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
