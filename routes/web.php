<?php

use App\Http\Controllers\ImapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/imap', [ImapController::class, 'imap']);

Route::get('/email-form', [ImapController::class, 'showEmailForm'])->name('email_form');
Route::post('/store-emails', [ImapController::class, 'storeFrom'])->name('store_emails');

Route::get('/set/{id}', [ImapController::class, 'setOrganizationalEmail'])->name('set_organizational_email');
Route::get('/get/{id}', [ImapController::class, 'getOrganizationalEmails'])->name('get_organizational_email');

Route::get('/excel/{id?}', [ImapController::class, 'getExcel'])->name('get_excel');

Route::get('/scrap', [ImapController::class, 'scrap'])->name('scrap');
Route::get('/file', [ImapController::class, 'file'])->name('file');
Route::get('/test/{id}', [ImapController::class, 'test'])->name('test');