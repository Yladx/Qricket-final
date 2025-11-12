<?php

use Illuminate\Support\Facades\Route;

// Feedback notification trigger (request attendees to leave feedback)
Route::post('/events/{event}/feedback/notify', [\App\Http\Controllers\Events\EventFeedbackController::class, 'notifyAttendees'])
    ->middleware(['auth'])
    ->name('events.feedback.notify');

// Event deletion - allow even if not verified
Route::prefix('organizer')->middleware('auth')->group(function () {
    Route::delete('/events/{id}', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'destroy'])->name('organizer.events.destroy');
});

Route::prefix('organizer')->middleware('verified.organizer')->group(function () {
    // Step 1 - Basic Info
    Route::get('/events/create', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'step1'])->name('organizer.events.step1');
    Route::get('/events/create/basicinfo', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'step1'])->name('organizer.events.create.basicinfo');
    Route::post('/events/step1', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'step1Store'])->name('organizer.events.step1.store');
    Route::get('/events/tag-category-mappings', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'getTagCategoryMappings'])->name('organizer.events.tag-category-mappings');
    Route::get('/events/existing-tags', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'getExistingTags'])->name('organizer.events.existing-tags');
    Route::post('/events/{id}/cancel', [\App\Http\Controllers\Organizer\EventManagement\BasicInfoController::class, 'cancel'])->name('organizer.events.cancel');

    // Step 2 - Layout
    Route::get('/events/create/layout', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'step2'])->name('organizer.events.create.layout');
    Route::post('/events/layout', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'step2Store'])->name('organizer.events.layout.store');
    Route::get('/events/layout/preview', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'layoutPreview'])->name('organizer.events.layout.preview');
    // Alias preview path for Step 2 (requested as /organizer/events/step2/preview)
    Route::get('/events/step2/preview/{id?}', [\App\Http\Controllers\Events\EventController::class, 'step2Preview'])->name('organizer.events.step2.preview');
    Route::post('/events/layout/preview/save', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'step2PreviewSave'])->name('organizer.events.layout.preview.save');
    Route::get('/events/layout/preview/load/{cloudPath}', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'step2PreviewLoad'])
        ->where('cloudPath', '.*')
        ->name('organizer.events.layout.preview.load');
    
    // Layout template rendering
    Route::post('/events/layouts/{layout}', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'renderLayout'])->name('organizer.events.layouts.render');
    Route::post('/events/upload-layout-image', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'uploadLayoutImage'])->name('organizer.events.upload-layout-image');
    Route::post('/events/get-fresh-image-url', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'getFreshImageUrl'])->name('organizer.events.get-fresh-image-url');
    Route::post('/events/update-session-layout', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'updateSessionLayout'])->name('organizer.events.update-session-layout');
    Route::post('/events/layout/autosave', [\App\Http\Controllers\Organizer\EventManagement\LayoutController::class, 'autosaveStep2'])->name('organizer.events.layout.autosave');

    // Step 3 - Tickets
    Route::get('/events/create/ticket', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'step3'])->name('organizer.events.create.ticket');
    Route::post('/events/ticket', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'step3Store'])->name('organizer.events.ticket.store');
    Route::post('/events/ticket/autosave', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'autosaveStep3'])->name('organizer.events.ticket.autosave');
    Route::post('/events/ticket/autosave/flush', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'autosaveFlush'])->name('organizer.events.ticket.autosave.flush');

    // Step 4 - Certificate
    Route::get('/events/create/certificate', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'step4'])->name('organizer.events.create.certificate');
    Route::post('/events/certificate', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'step4Store'])->name('organizer.events.certificate.store');
    Route::post('/events/certificate/autosave', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'autosaveCertificate'])->name('organizer.events.certificate.autosave');
    Route::get('/events/certificate-status', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'getCertificateStatus'])->name('organizer.events.certificate.status');
    Route::post('/events/certificate-status', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'updateCertificateStatus'])->name('organizer.events.certificate.status.update');
    Route::post('/events/upload-certificate', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'uploadCertificate'])->name('organizer.events.upload-certificate');
    Route::post('/events/remove-certificate', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'removeCertificate'])->name('organizer.events.remove-certificate');
    Route::get('/events/certificate/stream', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'streamCertificate'])->name('organizer.events.certificate.stream');
    Route::get('/events/certificate/asset/{file}', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'serveAsset'])->where('file','[A-Za-z0-9_\.-]+')->name('organizer.events.certificate.asset');
    Route::get('/events/create/certificate/preview', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'certificatePreview'])->name('organizer.events.create.certificate.preview');
    // Standalone print page for certificate (renders only the certificate template)
    Route::get('/events/create/certificate/print', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'certificatePrint'])
        ->name('organizer.events.create.certificate.print');
    Route::post('/events/certificate/upload-logo', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'uploadLogo']);
    Route::post('/events/certificate/remove-logo', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'removeLogo']);
    Route::post('/events/certificate/upload-signature', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'uploadSignature']);
    Route::post('/events/certificate/remove-signature', [\App\Http\Controllers\Organizer\EventManagement\CertificateController::class, 'removeSignature']);

    // Step 5 - Staff
    Route::get('/events/create/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'step5'])->name('organizer.events.create.staff');
    Route::get('/events/{event}/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'manage'])->name('organizer.events.staff');
    Route::post('/events/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'step5Store'])->name('organizer.events.staff.store');
    Route::post('/events/staff/autosave', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'autosaveStep5'])->name('organizer.events.staff.autosave');
    Route::post('/events/staff/autosave/flush', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'autosaveFlush'])->name('organizer.events.staff.autosave.flush');
    Route::get('/events/staff/search', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'searchStaff'])->name('organizer.events.staff.search');

    // Step 6 - Review
    Route::get('/events/create/review', [\App\Http\Controllers\Organizer\EventManagement\ReviewController::class, 'step6'])->name('organizer.events.create.review');
    Route::post('/events/review', [\App\Http\Controllers\Organizer\EventManagement\ReviewController::class, 'step6Store'])->name('organizer.events.review.store');
    
    // Subscription Management
    Route::get('/subscription/plans', [\App\Http\Controllers\Payment\SubscriptionController::class, 'plans'])->name('organizer.subscription.plans');
    Route::get('/subscription/current', [\App\Http\Controllers\Payment\SubscriptionController::class, 'current'])->name('organizer.subscription.current');
    Route::post('/subscription/subscribe', [\App\Http\Controllers\Payment\SubscriptionController::class, 'subscribe'])->name('organizer.subscription.subscribe');
    Route::post('/subscription/cancel', [\App\Http\Controllers\Payment\SubscriptionController::class, 'cancel'])->name('organizer.subscription.cancel');
    Route::post('/subscription/autorenew/toggle', [\App\Http\Controllers\Payment\SubscriptionController::class, 'toggleAutoRenew'])->name('organizer.subscription.autorenew.toggle');
    Route::get('/subscription/invoice', [\App\Http\Controllers\Payment\SubscriptionController::class, 'invoice'])->name('organizer.subscription.invoice');
    Route::get('/subscription/status', [\App\Http\Controllers\Payment\SubscriptionController::class, 'status'])->name('organizer.subscription.status');
});



