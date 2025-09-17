<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ApprovalController;


use App\Http\Controllers\KomplainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UnitController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\JenisKomplainController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\KomplainController as AdminKomplainController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|
| USER PAGE
|
*/
Route::get('/', [LandingController::class, 'index'])->name('login');