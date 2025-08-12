<?php

use App\Http\Controllers\Auth\WelcomeController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HoursController;
use Illuminate\Support\Facades\Route;
use Spatie\WelcomeNotification\WelcomesNewUsers;

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

Route::get('/', function () {
    return redirect('/login');
});

Route::group(['middleware' => ['web', WelcomesNewUsers::class]], function () {
    Route::get('welcome/{user}', [WelcomeController::class, 'showWelcomeForm'])->name('welcome');
    Route::post('welcome/{user}', [WelcomeController::class, 'savePassword'])->name('savePassword');
});

Route::middleware(['auth', 'user'])->group(function () {

    /**
     * ###########################################
     * Dashboard
     * ###########################################
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * ###########################################
     * Reports
     * ###########################################
     */
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    /**
     * ###########################################
     * Billing
     * ###########################################
     */
    Route::get('/bills/{localDate?}', [BillController::class, 'index'])->name('bills');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');

    /**
     * ###########################################
     * Invoices
     * ###########################################
     */
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('invoices.index');
    Route::get('/billing/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/billing/invoices/{invoice}/preview/{filename}', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/billing/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/billing/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    // Route::get('/invoices/{invoice}/pay', [CheckoutController::class, 'stripe'])->name('invoices.pay');
    Route::get('/billing/invoices/{invoice}/paymentSuccess', [CheckoutController::class, 'paymentSuccess'])->name('invoices.paymentSuccess');
    Route::post('/billing/invoices/{invoice}/pay', [CheckoutController::class, 'pay'])->name('invoices.confirmPay');

    /**
     * ###########################################
     * Payments
     * ###########################################
     */
    Route::get('/billing/payments', [PaymentController::class, 'index'])->name('payments');

    /**
     * ###########################################
     * Payment Methods
     * ###########################################
     */
    // Route::get('/paymentMethods', [PaymentMethodController::class, 'index'])->name('paymentMethods.index');
    Route::get('/billing/paymentMethods', [CheckoutController::class, 'paymentMethods'])->name('paymentMethods.index');
    // Route::get('/addBankAccount', [CheckoutController::class, 'addBankAccount'])->name('addBankAccount');
    Route::get('/paymentMethods/create', [CheckoutController::class, 'createPaymentMethod'])->name('paymentMethods.create');
    Route::get('/paymentMethods/createSuccess', [CheckoutController::class, 'createPaymentMethodSuccess'])->name('paymentMethods.createSuccess');
    Route::delete('/paymentMethods/{paymentMethod}', [CheckoutController::class, 'deletePaymentMethod'])->name('paymentMethods.destroy');
    // Route::get('/addBankAccountSuccess', [CheckoutController::class, 'addBankAccountSuccess'])->name('addBankAccountSuccess');
    // Route::get('/stripeCheckout', function ()
});

Route::prefix('admin')->middleware(['auth', 'admin', 'user'])->name('admin.')->group(function () {

    /**
     * ###########################################
     * Admin Dashboard
     * ###########################################
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * ###########################################
     * Admin Issues
     * ###########################################
     */
    Route::resource('issues', IssueController::class);
    Route::post('/issues/{issue}/archive', [IssueController::class, 'archive'])->name('issues.archive');
    Route::delete('/issues/{issue}/unarchive', [IssueController::class, 'unarchive'])->name('issues.unarchive');

    /**
     * ###########################################
     * Admin Reports
     * ###########################################
     */
    Route::resource('reports', ReportController::class)->except(['show']);
    

    /**
     * ###########################################
     * Admin Hours
     * ###########################################
     */
    // Route::get('/hours', [HoursController::class, 'index'])->name('hours.index');

    /**
     * ###########################################
     * Admin Work
     * ###########################################
     */
    Route::get('/work/{workEntry}/report', [HoursController::class, 'reportWorkCreate'])->name('hours.report.create');
    Route::post('/work/{workEntry}/report', [HoursController::class, 'reportWorkStore'])->name('hours.report.store');
    Route::post('/work/{workEntry}/unreport', [HoursController::class, 'unreport'])->name('hours.unreport');
    Route::resource('hours', HoursController::class)->parameters(['hour' => 'workEntry']);

    /**
     * ###########################################
     * Admin Invoices
     * ###########################################
     */
    Route::resource('invoices', InvoiceController::class)->except(['show']);
    Route::post('/invoice/{invoice}/approve', [InvoiceController::class, 'approve'])->name('invoices.approve');
    Route::post('/invoice/{invoice}/cancelApproval', [InvoiceController::class, 'cancelApproval'])->name('invoices.cancelApproval');
    // Need to add filename at the end so that it will save properly in some browsers
    Route::get('/invoices/{invoice}/preview/{filename}', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    /**
     * ###########################################
     * Admin Payments
     * ###########################################
     */
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/add/{invoice?}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/notifiy', [PaymentController::class, 'notify'])->name('payments.notify');
    Route::post('/payments/{payment}/invoice/{invoice}', [PaymentController::class, 'attach'])->name('payments.attach');

    /**
     * ###########################################
     * Admin Organizations
     * ###########################################
     */
    Route::resource('organizations', OrganizationController::class)->except(['destroy']);
    Route::post('organizations/{organization}/enableStripe', [OrganizationController::class, 'enableStripe'])->name('organizations.enableStripe');
    Route::post('changeOrganization', [OrganizationController::class, 'changeOrganization'])->name('changeOrganization');

    /**
     * ###########################################
     * Admin Users
     * ###########################################
     */
    Route::resource('users', UserController::class);
    Route::post('users/{user}/invite', [UserController::class, 'invite'])->name('users.invite');

    /**
     * ###########################################
     * Admin Emails
     * ###########################################
     */
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');

    /**
     * ###########################################
     * Admin Settings
     * ###########################################
     */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    /**
     * ###########################################
     * Admin Tools
     * ###########################################
     */
    // Route::get('/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
    

});

if (config('app.env') === 'local') {

    Route::name('dev.')->prefix('dev')->group(function () {
        Route::get('invoice/{invoice}', [DevController::class, 'invoice'])->name('invoice');
        Route::get('pay-invoice/{invoice}', [DevController::class, 'payInvoice'])->middleware('signed')->name('payInvoice');
        // Route::get('invoice/{invoice}', [DevController::class, 'pay-invoice'])->middleware('signed')->name('invoice');
        Route::get('account-summary-email/{organization?}', [DevController::class, 'accountSummaryEmail'])->name('accountSummaryEmail');
    });
}

require __DIR__ . '/auth.php';
