<?php

use App\Http\Controllers\GroupAdmin\GroupAdministratorController;
use App\Http\Controllers\GroupAdmin\GroupCandidateController;
use App\Http\Controllers\GroupAdmin\GroupController;
use App\Http\Controllers\GroupAdmin\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'group_admin'])->prefix('manage')->name('group_admin.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('group_admin.profile')->group(function () {
        Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{group}', [GroupController::class, 'show'])->whereNumber('group')->name('groups.show');
        Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->whereNumber('group')->name('groups.edit');
        Route::put('/groups/{group}', [GroupController::class, 'update'])->whereNumber('group')->name('groups.update');
        Route::put('/groups/{group}/activate', [GroupController::class, 'activate'])->whereNumber('group')->name('groups.activate');
        Route::put('/groups/{group}/deactivate', [GroupController::class, 'deactivate'])->whereNumber('group')->name('groups.deactivate');

        Route::get('/groups/{group}/admins', [GroupAdministratorController::class, 'index'])->whereNumber('group')->name('groups.admins.index');
        Route::post('/groups/{group}/admins', [GroupAdministratorController::class, 'store'])->whereNumber('group')->name('groups.admins.store');
        Route::delete('/groups/{group}/admins/{admin}', [GroupAdministratorController::class, 'destroy'])->whereNumber(['group', 'admin'])->name('groups.admins.destroy');

        Route::get('/groups/{group}/candidates', [GroupCandidateController::class, 'index'])->whereNumber('group')->name('groups.candidates.index');
        Route::post('/groups/{group}/candidates', [GroupCandidateController::class, 'store'])->whereNumber('group')->name('groups.candidates.store');
        Route::get('/groups/{group}/candidates/{candidate}', [GroupCandidateController::class, 'show'])->whereNumber(['group', 'candidate'])->name('groups.candidates.show');
        Route::delete('/groups/{group}/candidates/{candidate}', [GroupCandidateController::class, 'destroy'])->whereNumber(['group', 'candidate'])->name('groups.candidates.destroy');
        Route::put('/groups/{group}/candidates/{candidate}/approve', [GroupCandidateController::class, 'approve'])->whereNumber(['group', 'candidate'])->name('groups.candidates.approve');
        Route::put('/groups/{group}/candidates/{candidate}/suspend', [GroupCandidateController::class, 'suspend'])->whereNumber(['group', 'candidate'])->name('groups.candidates.suspend');
        Route::put('/groups/{group}/candidates/{candidate}/reactivate', [GroupCandidateController::class, 'reactivate'])->whereNumber(['group', 'candidate'])->name('groups.candidates.reactivate');
        Route::put('/groups/{group}/candidates/{candidate}/notes', [GroupCandidateController::class, 'updateNotes'])->whereNumber(['group', 'candidate'])->name('groups.candidates.notes');
    });
});
