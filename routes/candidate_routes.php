<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Candidate\AccountController;
use App\Http\Controllers\Candidate\CareerDetailsController;
use App\Http\Controllers\Candidate\DiscoveryController;
use App\Http\Controllers\Candidate\EducationDetailsController;
use App\Http\Controllers\Candidate\FamilyDetailsController;
use App\Http\Controllers\Candidate\FavoriteCandidateController;
use App\Http\Controllers\Candidate\GroupMembershipController;
use App\Http\Controllers\Candidate\LikedCandidateController;
use App\Http\Controllers\Candidate\LookupController;
use App\Http\Controllers\Candidate\PartnerRequirementsController;
use App\Http\Controllers\Candidate\PassedCandidateController;
use App\Http\Controllers\Candidate\PersonalDetailsController;
use App\Http\Controllers\Candidate\PhotoController;
use App\Http\Controllers\Candidate\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'candidate'])->group(function () {
    Route::get('/profile/create', [ProfileController::class, 'create'])->name('candidate.profile.create');
    Route::post('/profile', [ProfileController::class, 'store'])->name('candidate.profile.store');

    Route::middleware('candidate.profile')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('candidate.profile.show');
        Route::put('/profile/status', [ProfileController::class, 'updateStatus'])->name('candidate.profile.status');
        Route::put('/profile/visibility', [ProfileController::class, 'updateVisibility'])->name('candidate.profile.visibility');

        Route::get('/profile/photos', [PhotoController::class, 'index'])->name('candidate.photos.index');
        Route::post('/profile/photos', [PhotoController::class, 'store'])
            ->middleware('throttle:15,1')
            ->name('candidate.photos.store');
        Route::post('/profile/photos/order', [PhotoController::class, 'reorder'])->name('candidate.photos.reorder');
        Route::put('/profile/photos/blur', [PhotoController::class, 'updateBlur'])->name('candidate.photos.blur');
        Route::post('/profile/photos/{photo}/replace', [PhotoController::class, 'update'])
            ->middleware('throttle:15,1')
            ->whereNumber('photo')
            ->name('candidate.photos.update');
        Route::delete('/profile/photos/{photo}', [PhotoController::class, 'destroy'])
            ->whereNumber('photo')
            ->name('candidate.photos.destroy');
        Route::post('/profile/photos/{photo}/main', [PhotoController::class, 'main'])
            ->whereNumber('photo')
            ->name('candidate.photos.main');

        Route::get('/profile/personal', [PersonalDetailsController::class, 'edit'])->name('candidate.profile.personal.edit');
        Route::put('/profile/personal', [PersonalDetailsController::class, 'update'])->name('candidate.profile.personal.update');
        Route::get('/profile/education', [EducationDetailsController::class, 'edit'])->name('candidate.profile.education.edit');
        Route::put('/profile/education', [EducationDetailsController::class, 'update'])->name('candidate.profile.education.update');
        Route::get('/profile/career', [CareerDetailsController::class, 'edit'])->name('candidate.profile.career.edit');
        Route::put('/profile/career', [CareerDetailsController::class, 'update'])->name('candidate.profile.career.update');
        Route::get('/profile/family', [FamilyDetailsController::class, 'edit'])->name('candidate.profile.family.edit');
        Route::put('/profile/family', [FamilyDetailsController::class, 'update'])->name('candidate.profile.family.update');
        Route::get('/profile/partner', [PartnerRequirementsController::class, 'edit'])->name('candidate.profile.partner.edit');
        Route::put('/profile/partner', [PartnerRequirementsController::class, 'update'])->name('candidate.profile.partner.update');

        Route::get('/groups', [GroupMembershipController::class, 'index'])->name('candidate.groups.index');
        Route::delete('/groups/{group}', [GroupMembershipController::class, 'destroy'])
            ->whereNumber('group')
            ->name('candidate.groups.leave');

        Route::get('/candidates', [DiscoveryController::class, 'index'])->name('candidate.discovery.index');
        Route::get('/candidates/{candidate}', [DiscoveryController::class, 'show'])
            ->whereNumber('candidate')
            ->name('candidate.discovery.show');

        Route::get('/likes', [LikedCandidateController::class, 'index'])->name('candidate.likes.index');
        Route::post('/candidates/{candidate}/like', [LikedCandidateController::class, 'store'])
            ->whereNumber('candidate')
            ->name('candidate.likes.store');
        Route::delete('/candidates/{candidate}/like', [LikedCandidateController::class, 'destroy'])
            ->whereNumber('candidate')
            ->name('candidate.likes.destroy');

        Route::get('/favorites', [FavoriteCandidateController::class, 'index'])->name('candidate.favorites.index');
        Route::post('/candidates/{candidate}/favorite', [FavoriteCandidateController::class, 'store'])
            ->whereNumber('candidate')
            ->name('candidate.favorites.store');
        Route::delete('/candidates/{candidate}/favorite', [FavoriteCandidateController::class, 'destroy'])
            ->whereNumber('candidate')
            ->name('candidate.favorites.destroy');

        Route::get('/passed', [PassedCandidateController::class, 'index'])->name('candidate.passed.index');
        Route::post('/candidates/{candidate}/pass', [PassedCandidateController::class, 'store'])
            ->whereNumber('candidate')
            ->name('candidate.passed.store');
        Route::delete('/candidates/{candidate}/pass', [PassedCandidateController::class, 'destroy'])
            ->whereNumber('candidate')
            ->name('candidate.passed.destroy');

        Route::get('/account', [AccountController::class, 'edit'])->name('candidate.account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('candidate.account.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('candidate.account.password');
        Route::post('/account/deactivate', [AccountController::class, 'deactivate'])->name('candidate.account.deactivate');

        Route::get('/lookups/states', [LookupController::class, 'states'])->name('candidate.lookups.states');
        Route::get('/lookups/cities', [LookupController::class, 'cities'])->name('candidate.lookups.cities');
        Route::get('/lookups/sects', [LookupController::class, 'sects'])->name('candidate.lookups.sects');
        Route::get('/lookups/ethnicities', [LookupController::class, 'ethnicities'])->name('candidate.lookups.ethnicities');
    });
});
