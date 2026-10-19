<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, CondominiumController, PrivilegeController, RoleController};

Route::post('login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('user', [AuthController::class, 'user']); Route::put('profile', [AuthController::class, 'updateProfile']); Route::put('password', [AuthController::class, 'changePassword']); Route::post('logout', [AuthController::class, 'logout']);
    Route::get('export/{module}', [CondominiumController::class, 'export'])->middleware('role:Administrador');
    Route::get('visits', [CondominiumController::class, 'visits'])->middleware('role:Administrador,Residente,Guardia');
    Route::get('visits/{visit}', [CondominiumController::class, 'showVisit'])->middleware('role:Administrador,Residente,Guardia');
    // La residencia recibe solamente su propia vivienda para registrar una visita.
    Route::get('homes', [CondominiumController::class, 'homes'])->middleware('role:Administrador,Residente');
    Route::get('homes/{home}', [CondominiumController::class, 'showHome'])->middleware('role:Administrador,Residente');
    Route::post('visits/{visit}/authorize', [CondominiumController::class, 'authorizeVisit'])->middleware('role:Administrador,Residente');
    Route::post('visits', [CondominiumController::class, 'storeVisit'])->middleware('role:Administrador,Residente');
    Route::post('visits/{visit}/entry', [CondominiumController::class, 'entry'])->middleware('role:Administrador,Guardia'); Route::post('visits/{visit}/exit', [CondominiumController::class, 'exit'])->middleware('role:Administrador,Guardia'); Route::post('payments/{payment}/pay', [CondominiumController::class, 'pay'])->middleware('role:Administrador');
    Route::middleware('role:Administrador')->group(function () {
        Route::get('dashboard', [CondominiumController::class, 'dashboard']);
        Route::get('residents', [CondominiumController::class, 'residents']);
        Route::get('residents/{resident}', [CondominiumController::class, 'showResident']);
        Route::get('payments', [CondominiumController::class, 'payments']);
        Route::get('payments/{payment}', [CondominiumController::class, 'showPayment']);
        Route::get('incidents', [CondominiumController::class, 'incidents']);
        Route::get('incidents/{incident}', [CondominiumController::class, 'showIncident']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('privileges', [PrivilegeController::class, 'index']);
        Route::post('privileges', [PrivilegeController::class, 'store']);
        foreach (['Home' => 'homes', 'Resident' => 'residents', 'Visit' => 'visits', 'Payment' => 'payments', 'Incident' => 'incidents'] as $name => $path) {
            $parameter = strtolower($name); Route::post($path, [CondominiumController::class, "store{$name}"]); Route::put("{$path}/{{$parameter}}", [CondominiumController::class, "update{$name}"]); Route::delete("{$path}/{{$parameter}}", [CondominiumController::class, "destroy{$name}"]);
        }
    });
});
