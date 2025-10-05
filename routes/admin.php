<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DashboardController_old;




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

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::get('/login', [AuthController::class, 'index']);
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin']], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [DashboardController_old::class, 'index'])->name('transactions');
    Route::any('/transactions/filter/', [DashboardController::class, 'transactionFilter'])->name('transactions.filter');
    Route::get('/transactions/{id}/details', [DashboardController::class, 'details'])->name('details')->where(array('id' => '[0-9]+'));
    Route::get('/payment/{id}/details', [DashboardController::class, 'paymentDetails'])->name('payment.details')->where(array('id' => '[0-9]+'));

    Route::get('/clear-cache', function () {
        $exitCode = Artisan::call('config:cache');
        $exitCode = Artisan::call('cache:clear');
        $exitCode = Artisan::call('view:clear');
        $exitCode = Artisan::call('route:cache');
    });

    Route::get('/payments', [DashboardController::class, 'payments'])->name('payments');
    Route::any('/orders', [DashboardController::class, 'order'])->name('orders');
    Route::get('/orders/{id}/sendNotification', [DashboardController::class, 'sendOrderNotification'])->name('notification.trigger');
    Route::get('/orders/{id}/details', [DashboardController::class, 'orderDetails'])->name('order.details')->where(array('id' => '[0-9]+'));

    Route::get('/refunds', [DashboardController::class, 'getRefunds'])->name('refunds');

    //invoices
    Route::get('/invoices', [DashboardController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{id}/details', [DashboardController::class, 'inDetails'])->name('invoices.details')->where(array('id' => '[0-9]+'));

    //disputes
    Route::get('/disputes', [DashboardController::class, 'disputes'])->name('disputes');
    Route::get('/disputes/{id}/details', [DashboardController::class, 'disputeDetails'])->name('dispute.details')->where(array('id' => '[0-9]+'));
    Route::post('/order/refund', [DashboardController::class, 'refundBuyer'])->name('buyer.refund');
    Route::post('/order/replace', [DashboardController::class, 'replaceOrder'])->name('order.replace');
    Route::post('/order/cancel', [DashboardController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/order/track', [DashboardController::class, 'trackOrder'])->name('order.track');
    Route::post('/order/requestDelivery', [DashboardController::class, 'requestDelivery'])->name('order.requestDelivery');
    Route::post('/order/requestPickup', [DashboardController::class, 'requestPickup'])->name('order.requestPickup');
    Route::post('/order/markAsDelivered', [DashboardController::class, 'markAsDelivered'])->name('order.markAsDelivered');

    //discounts
    Route::get('/discounts', [DashboardController::class, 'discounts'])->name('discounts');
    Route::get('/discounts/create', [DashboardController::class, 'createDiscount'])->name('discount.create');
    Route::post('/discounts/add', [DashboardController::class, 'addDiscount'])->name('discount.add');
    Route::get('/fetchMerchantProducts/{id}', [DashboardController::class, 'fetchMerchantProducts'])->name('merchant.fetchProduct')->where(array('id' => '[0-9]+'));
    Route::get('/discounts/{id}/edit', [DashboardController::class, 'editDiscount'])->name('discount.edit')->where(array('id' => '[0-9]+'));
    Route::post('/discounts/update', [DashboardController::class, 'updateDiscount'])->name('discount.update');
    Route::post('/discounts/delete', [DashboardController::class, 'removeDiscount'])->name('discount.delete');

    //coupons
    Route::get('/coupons', [DashboardController::class, 'coupons'])->name('coupons');
    Route::get('/coupons/create', [DashboardController::class, 'createCoupon'])->name('coupon.create');
    Route::post('/coupons/add', [DashboardController::class, 'addCoupon'])->name('coupon.add');
    Route::get('/coupons/{id}/edit', [DashboardController::class, 'editCoupon'])->name('coupon.edit')->where(array('id' => '[0-9]+'));
    Route::post('/coupons/update', [DashboardController::class, 'updateCoupon'])->name('coupon.update');
    Route::post('/coupons/delete', [DashboardController::class, 'removeCoupon'])->name('coupon.delete');

    //subscriptions
    Route::any('/subscriptions', [DashboardController::class, 'subscriptions'])->name('subscriptions');

    //shipments
    Route::get('/shipment', [DashboardController::class, 'shipment'])->name('shipment.all');
    Route::get('/shipment/{md}', [DashboardController::class, 'shipmentBooked'])->name('shipmet.status');
    Route::get('/sendy/requests', [DashboardController::class, 'sendyRequests'])->name('sendy.requests');
    Route::get('/shipment/{id}/details', [DashboardController::class, 'shipmentDetails'])->name('shipment.details')->where(array('id' => '[0-9]+'));



    //Audit trails
    Route::get('/audit', [DashboardController::class, 'audit_trails'])->name('audit');
    Route::get('/audit/{id}/details', [DashboardController::class, 'auditdetails'])->name('audit.details');
    //Audit transactions
    Route::get('/audit/transactions', [DashboardController::class, 'index'])->name('audit.transaction');
    Route::get('/audit/orders', [DashboardController::class, 'order'])->name('audit.order');
    Route::get('/audit/invoice', [DashboardController::class, 'invoices'])->name('audit.invoice');

    //users
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::get('/customers/{id}/details', [DashboardController::class, 'auditdetails'])->name('audit.details');

    //products
    Route::get('/products/{id}/edit', [DashboardController::class, 'editProduct'])->name('product.edit')->where(array('id' => '[0-9]+'));
    Route::get('/product/requests', [DashboardController::class, 'productRequests'])->name('product.requests');
    Route::post('/products/update', [DashboardController::class, 'updateProduct'])->name('product.update');
    Route::post('/products/delete', [DashboardController::class, 'deleteProduct'])->name('product.delete');
    Route::post('/products/add', [DashboardController::class, 'addProduct'])->name('product.add');
    Route::get('/products/{merchantID}/create', [DashboardController::class, 'createProduct'])->name('product.create')->where(array('merchantID' => '[0-9]+'));

    //address
    Route::get('/pickupAddress/{merchantID}/create', [DashboardController::class, 'createPickupAddress'])->name('pickupAddress.create')->where(array('merchantID' => '[0-9]+'));
    Route::post('/pickupAddress/add', [DashboardController::class, 'addPickupAddress'])->name('pickupAddress.add');
    Route::get('/storeAddress/{merchantID}/create', [DashboardController::class, 'createStoreAddress'])->name('storeAddress.create')->where(array('merchantID' => '[0-9]+'));
    Route::post('/storeAddress/add', [DashboardController::class, 'addStoreAddress'])->name('storeAddress.add');

    //store
    Route::get('/store/{id}/edit', [DashboardController::class, 'editStore'])->name('store.edit')->where(array('id' => '[0-9]+'));
    Route::post('/store/update', [DashboardController::class, 'updateStore'])->name('store.update');
    Route::any('/store/visits', [DashboardController::class, 'storeVisits'])->name('store.visits');

    //users
    Route::any('/customers/{type}', [DashboardController::class, 'customers'])->name('customers');
    Route::get('/customer/get', [DashboardController::class, 'addCustomerForm'])->name('customer.add.get');
    Route::get('/customer/bulk/upload', [DashboardController::class, 'bulkCustomerForm'])->name('customer.bulk.form');
    Route::get('/bulk/sample/download', [DashboardController::class, 'downloadSampleSheet'])->name('sample.download');
    Route::post('/customer/add', [DashboardController::class, 'addCustomer'])->name('customer.add');
    Route::post('/customer/bulk/add', [DashboardController::class, 'addBulkCustomer'])->name('customer.bulk.add');
    Route::get('/customers/{id}/details', [DashboardController::class, 'customerdetails'])->name('cdetails');
    Route::post('/customers/{id}/details', [DashboardController::class, 'block'])->name('customer.block');
    Route::post('/store/approval', [DashboardController::class, 'storeApproval'])->name('store.approval');

    Route::get('/newsletter/create', [DashboardController::class, 'createNewsletter'])->name('newsletter.create');
    Route::post('/newsletter/send', [DashboardController::class, 'sendNewsletter'])->name('newsletter.send');
    Route::post('/newsletter/getUserGroup', [DashboardController::class, 'getUserGroup'])->name('newsletter.getUserGroup');

    //Admin users
    Route::get('/admin', [AdminController::class, 'admin'])->name('admins');
    Route::post('/admin', [AdminController::class, 'saveAdmin'])->name('admin.post');

    Route::get('/admins', [AdminController::class, 'getAdmins'])->name('admin.manage');
    Route::get('/admins/{id}/details', [AdminController::class, 'show'])->name('admin.show');
    Route::get('/admins/{id}/delete', [AdminController::class, 'delete'])->name('admin.delete');


    Route::get('/password', [AdminController::class, 'password'])->name('password');
    Route::post('/password/update', [AdminController::class, 'passwordUpdate'])->name('admin.password.update');

    Route::get('/filter/data', [DashboardController::class, 'filterTransaction'])->name('filter');
    //Route::get('/download_order', [DashController::class, 'downloadOrder'])->name('download.order');



    /**
     *
     * COMBS AND CLIPPPERS
     *
     */

    Route::any('/appointments', [DashboardController::class, 'appointmentIndex'])->name('appointments')->middleware('check.panel.role:appointments,view_appointments|superAdmin');
    Route::get('/appointments/{id}/show', [DashboardController::class, 'showAppointment'])->name('appointment.show')->middleware('check.panel.role:appointments,view_appointments|superAdmin');
    Route::put('/appointments/{id}/update', [DashboardController::class, 'updateAppointment'])->name('admin.appointments.update')->middleware('check.panel.role:appointments,edit_appointments|superAdmin');
    Route::delete('/appointments/{id}/destroy', [DashboardController::class, 'destroyAppointment'])->name('admin.appointments.destroy')->middleware('check.panel.role:appointments,delete_appointments|superAdmin');

    // Accounts
    Route::any('/accounts', [DashboardController::class, 'accountIndex'])->name('accounts')->middleware('check.panel.role:accounts,view_accounts|superAdmin');
    Route::get('/accounts/{id}/show', [DashboardController::class, 'showAccount'])->name('account.show')->middleware('check.panel.role:accounts,view_accounts|superAdmin');
    Route::delete('/accounts/{id}/destroy', [DashboardController::class, 'destroyAccount'])->name('admin.accounts.destroy')->middleware('check.panel.role:accounts,delete_accounts|superAdmin');

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/appointments', [DashboardController::class, 'appointmentPayments'])->name('appointment.payments')->middleware('check.panel.role:payments,view_appointment_payment|superAdmin');

        // booth rent payment
        Route::get('/booth-rent', [DashboardController::class, 'boothRentPayments'])->name('boothrent.payments')->middleware('check.panel.role:payments,view_boothrent_payment|superAdmin');
        Route::put('/booth-rent/{id}/mark-as-paid', [DashboardController::class, 'markBoothRentAsPaid'])->name('admin.markBoothRentAsPaid')->middleware('check.panel.role:payments,mark_aspaid|superAdmin');
        Route::put('/booth-rent/{id}/remind-tenant', [DashboardController::class, 'sendBoothRentReminder'])->name('admin.sendBoothRentReminder')->middleware('check.panel.role:payments,send_boothrent_reminder|superAdmin');
        Route::get('/withdrawals', [DashboardController::class, 'withdrawalPayments'])->name('withdrawal.payments')->middleware('check.panel.role:payments,view_withdrawal_payment|superAdmin');
    });

    // Stores
    Route::prefix('stores')->group(function () {
        Route::get('/', [DashboardController::class, 'stores'])->name('stores')->middleware('check.panel.role:stores,view_stores|superAdmin');
        Route::get('/service-types', [DashboardController::class, 'serviceTypes'])->name('service.types')->middleware('check.panel.role:stores,view_service_types|superAdmin');
        Route::post('/service-types', [DashboardController::class, 'createServiceType'])->name('admin.serviceType.create')->middleware('check.panel.role:stores,create_service_types|superAdmin');
        Route::put('/admin/service-type/{id}/update', [DashboardController::class, 'updateServiceType'])->name('admin.serviceType.update')->middleware('check.panel.role:stores,edit_service_types|superAdmin');
        Route::delete('/service-types/{id}/delete', [DashboardController::class, 'destroyServiceType'])->name('admin.storeServiceType.destroy')->middleware('check.panel.role:stores,delete_service_types|superAdmin');
        Route::get('/stores/{id}/show', [DashboardController::class, 'showStore'])->name('admin.stores.show')->middleware('check.panel.role:stores,view_stores|superAdmin');
        Route::delete('/stores/{id}/delete', [DashboardController::class, 'deleteStore'])->name('admin.stores.destroy')->middleware('check.panel.role:stores,delete_store|superAdmin');

    });

    // Reports
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [DashboardController::class, 'chooseReport'])->name('choose')->middleware('check.panel.role:reports,view_reports|superAdmin');

        Route::get('/general', [DashboardController::class, 'generalReport'])->name('general')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/appointments', [DashboardController::class, 'appointmentReports'])->name('appointments')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/booth-rentals', [DashboardController::class, 'boothRentalReports'])->name('boothRentals')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/booth-rental-payments', [DashboardController::class, 'boothRentalPaymentReports'])->name('boothRentalPayments')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/internal-transactions', [DashboardController::class, 'internalTransactionReports'])->name('internalTransactions')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/bank-details', [DashboardController::class, 'bankDetailsReports'])->name('bankDetails')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/wallet-transactions', [DashboardController::class, 'walletTransactionReports'])->name('walletTransactions')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/wallets', [DashboardController::class, 'walletsReports'])->name('wallets')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/users', [DashboardController::class, 'usersReports'])->name('users')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/stores', [DashboardController::class, 'storesReports'])->name('stores')->middleware('check.panel.role:reports,view_reports|superAdmin');
        Route::get('/withdrawals', [DashboardController::class, 'withdrawalReports'])->name('withdrawals')->middleware('check.panel.role:reports,view_reports|superAdmin');

        // download routes
        Route::get('/general/download/excel', [ReportController::class, 'downloadGeneralReportExcel'])->name('general.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/general/download/pdf', [ReportController::class, 'downloadGeneralReportPdf'])->name('general.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/appointments/download/excel', [ReportController::class, 'downloadAppointmentReportExcel'])->name('appointments.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/appointments/download/pdf', [ReportController::class, 'downloadAppointmentReportPdf'])->name('appointments.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/boothRentals/download/excel', [ReportController::class, 'downloadBoothRentalReportExcel'])->name('boothRentals.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/boothRentals/download/pdf', [ReportController::class, 'downloadBoothRentalReportPdf'])->name('boothRentals.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/boothRentalPayments/download/excel', [ReportController::class, 'downloadBoothRentalPaymentReportExcel'])->name('boothRentalPayments.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/boothRentalPayments/download/pdf', [ReportController::class, 'downloadBoothRentalPaymentReportPdf'])->name('boothRentalPayments.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/internalTransactions/download/excel', [ReportController::class, 'downloadInternalTransactionReportExcel'])->name('internalTransactions.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/internalTransactions/download/pdf', [ReportController::class, 'downloadInternalTransactionReportPdf'])->name('internalTransactions.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/bankDetails/download/excel', [ReportController::class, 'downloadBankDetailsReportExcel'])->name('bankDetails.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/bankDetails/download/pdf', [ReportController::class, 'downloadBankDetailsReportPdf'])->name('bankDetails.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/walletTransactions/download/excel', [ReportController::class, 'downloadWalletTransactionReportExcel'])->name('walletTransactions.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/walletTransactions/download/pdf', [ReportController::class, 'downloadWalletTransactionReportPdf'])->name('walletTransactions.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/wallets/download/excel', [ReportController::class, 'downloadWalletsReportExcel'])->name('wallets.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/wallets/download/pdf', [ReportController::class, 'downloadWalletsReportPdf'])->name('wallets.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/withdrawal/download/excel', [ReportController::class, 'downloadWithdrawalReportExcel'])->name('withdrawals.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/withdrawal/download/pdf', [ReportController::class, 'downloadWithdrawalReportPdf'])->name('withdrawals.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/user/download/excel', [ReportController::class, 'downloadUsersReportExcel'])->name('users.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/user/download/pdf', [ReportController::class, 'downloadUsersReportPdf'])->name('users.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/store/download/excel', [ReportController::class, 'downloadStoresReportExcel'])->name('stores.download.excel')->middleware('check.panel.role:reports,download_reports|superAdmin');
        Route::get('/store/download/pdf', [ReportController::class, 'downloadStoresReportPdf'])->name('stores.download.pdf')->middleware('check.panel.role:reports,download_reports|superAdmin');
    });



    //Blogs
    Route::get('/blog-category', [DashboardController::class, 'blogCategory'])->name('blog.category.all')->middleware('check.panel.role:blogs,view_blog_categories|superAdmin');
    Route::get('/blog-category/create', [DashboardController::class, 'blogCategoryCreate'])->name('blog.category.create')->middleware('check.panel.role:blogs,create_blog_categories|superAdmin');
    Route::post('/blog-category/add', [DashboardController::class, 'blogCategoryAdd'])->name('blog.category.add')->middleware('check.panel.role:blogs,create_blog_categories|superAdmin');
    Route::get('/blog-category/{id}/show', [DashboardController::class, 'blogCategoryShow'])->name('blog.category.show')->middleware('check.panel.role:blogs,view_blog_categories|superAdmin');
    Route::post('/blog-category/{id}/edit', [DashboardController::class, 'blogCategoryEdit'])->name('blog.category.edit')->middleware('check.panel.role:blogs,edit_blog_categories|superAdmin');
    Route::get('/blog-category/{id}/delete', [DashboardController::class, 'blogCategoryDelete'])->name('blog.category.delete')->where(array('id' => '[0-9]+'))->middleware('check.panel.role:blogs,delete_blog_categories|superAdmin');

    Route::get('/blog', [DashboardController::class, 'blog'])->name('blog.all')->middleware('check.panel.role:blogs,view_blogs|superAdmin');
    Route::get('/blog/create', [DashboardController::class, 'blogCreate'])->name('blog.create')->middleware('check.panel.role:blogs,create_blogs|superAdmin');
    Route::post('/blog/add', [DashboardController::class, 'blogAdd'])->name('blog.add')->middleware('check.panel.role:blogs,create_blogs|superAdmin');
    Route::get('/blog/{id}/show', [DashboardController::class, 'blogShow'])->name('blog.show')->middleware('check.panel.role:blogs,view_blogs|superAdmin');
    Route::post('/blog/{id}/edit', [DashboardController::class, 'blogEdit'])->name('blog.edit')->middleware('check.panel.role:blogs,edit_blogs|superAdmin');
    Route::get('/blog/{id}/delete', [DashboardController::class, 'blogDelete'])->name('blog.delete')->where(array('id' => '[0-9]+'))->middleware('check.panel.role:blogs,delete_blogs|superAdmin');

    Route::name('admin.')->group(function () {
        Route::get('/management', [AdminController::class, 'index'])->name('management')->middleware('check.panel.role:admin,view_admins|superAdmin');
        Route::post('/admin', [AdminController::class, 'store'])->name('admin.create')->middleware('check.panel.role:admin,create_admins|superAdmin');
        Route::put('/admin/{id}', [AdminController::class, 'update'])->name('admin.update')->middleware('check.panel.role:admin,edit_admins|superAdmin');
        Route::put('/admin/{id}/roles', [AdminController::class, 'updateRoles'])->name('admin.updateRoles')->middleware('check.panel.role:admin,edit_admins|superAdmin');
        Route::delete('/admin/{id}', [AdminController::class, 'destroy'])->name('admin.destroy')->middleware('check.panel.role:admin,delete_admins|superAdmin');

        // Role Management
        Route::post('/role', [RoleController::class, 'store'])->name('role.create')->middleware('check.panel.role:role,create_roles|superAdmin');
        Route::put('/role/{roleName}', [RoleController::class, 'update'])->name('role.update')->middleware('check.panel.role:role,edit_roles|superAdmin');
        Route::delete('/role/{roleName}', [RoleController::class, 'destroy'])->name('role.destroy')->middleware('check.panel.role:role,delete_roles|superAdmin');

    });

});