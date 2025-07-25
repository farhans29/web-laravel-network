<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\inventory\InvListController;
use App\Http\Controllers\profile\ProfileController;
use App\Http\Controllers\salesorder\NewCustomerRequestController;
use App\Http\Controllers\salesorder\SalesOrderController;
use App\Http\Controllers\SearchProductController;

use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\TrafficCollectorController;
use App\Http\Controllers\SupportController;

use Faker\Guesser\Name;

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


// Route::redirect('/', 'login');
Route::redirect('/','dashboard');

Route::get('/inventory', [SearchProductController::class, 'index'])->name('search-product');
Route::get('/inventory/getdata', [SearchProductController::class, 'getData'])->name('search-product.getdata');
Route::get('/inventory/getdetail/{code}', [SearchProductController::class, 'getDetail'])->name('search-product.getdetail');
// TRAFFIC COLLECTOR
Route::get('/traffic-collector', [TrafficCollectorController::class, 'collectTrafficData']);
Route::get('/traffic-collector-daily', [TrafficCollectorController::class, 'collectTrafficDataDaily']);

// Json Data
Route::prefix('mikrotik')->group(function () {
        Route::get('/interfaces/getDataJson', [MikrotikController::class, 'getInterfacesDataJson'])->name('mikrotik.interfaces-data-json');  
        Route::get('/l2tp/getDataJson', [MikrotikController::class, 'getL2TPDataJson'])->name('mikrotik.l2tp-data-json');  
        Route::get('/ppp/getDataJson', [MikrotikController::class, 'getPPPSecretsDataJson'])->name('mikrotik.ppp-secrets-data-json');  
    });

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // ->middleware('checkRoleUser:500,501')

    Route::post('/update-gcal', [ProfileController::class, 'update'])->name('update.gcal');

    Route::prefix('inventory')->group(function () {
        Route::get('/invlist', [InvListController::class, 'index'])->name('invlist');
        Route::get('/invlist/getdata', [InvListController::class, 'getData'])->name('invlist.getdata');
        Route::get('/invlist/getdetail/{code}', [InvListController::class, 'getDetail'])->name('invlist.getdetail');
        Route::get('/invlist/updatepage/{code}', [InvListController::class, 'updatePage'])->name('invlist.updatepage');
        Route::post('/invlist/update/{code}', [InvListController::class, 'update'])->name('invlist.update');
        Route::get('/invlist/file1/{code}', [InvListController::class, 'viewPhoto1'])->name('invlist.photo1');
        Route::get('/invlist/file2/{code}', [InvListController::class, 'viewPhoto2'])->name('invlist.photo2');
        Route::get('/invlist/file3/{code}', [InvListController::class, 'viewPhoto3'])->name('invlist.photo3');
    });

    // Mikrotik API
    Route::prefix('mikrotik')->group(function () {
        //Router List
        Route::get('/routers', [MikrotikController::class, 'getAllRouters'])->name('mikrotik.routers');
        Route::get('/proxy-image/{router}/{period}', function ($router, $period) {
            $routerModel = \App\Models\Router::where('idrouter', $router)->firstOrFail();

            $url = "http://{$routerModel->ip}:{$routerModel->web_port}/graphs/iface/bridge/{$period}.gif";

            try {
                $response = Http::timeout(5)->get($url);

                if (!$response->ok()) {
                    abort(404, 'Image not found on router');
                }

                return response($response->body())
                    ->header('Content-Type', 'image/gif');
            } catch (\Exception $e) {
                abort(500, 'Failed to fetch image');
            }
        });

        //Interface List
        Route::get('/interface/{routerId}', [MikrotikController::class, 'getInterface'])->name('mikrotik.interface');
        Route::get('/interfaces/{routerId}', [MikrotikController::class, 'getInterfaces'])->name('mikrotik.interfaces');
        // Route::get('/interfaces/getDataJson', [MikrotikController::class, 'getInterfacesDataJson'])->name('mikrotik.interfaces-data-json'); 
        
        //Device List
        Route::get('/device/{routerId}', [MikrotikController::class, 'getConnectedDevice'])->name('mikrotik.device');
        Route::get('/devices/getData', [MikrotikController::class, 'getConnectedDevicesData'])->name('mikrotik.devices-data');  
        Route::get('/devices/{routerId}', [MikrotikController::class, 'getConnectedDevices'])->name('mikrotik.devices');   
        Route::post('/devices/make-static/{leaseId}/{routerId}', [MikrotikController::class, 'setStatic'])->name('mikrotik.set-static');
        Route::post('/devices/delete-static/{leaseId}/{routerId}', [MikrotikController::class, 'deleteStatic'])->name('mikrotik.delete-static');
        
        // Firewall Master Data List
        Route::get('/firewall/list/{routerId}', [MikrotikController::class, 'getFirewall'])->name('mikrotik.firewall-page');
        Route::get('/firewall-master/list/{routerId}', [MikrotikController::class, 'getFirewallMaster'])->name('mikrotik.firewall-master-page');
        Route::post('/firewall/insert/{routerId}', [MikrotikController::class, 'insertIntoFirewall'])->name('mikrotik.insert-firewall');
        Route::post('/firewall/update/{id}', [MikrotikController::class, 'updateFirewall'])->name('mikrotik.update-firewall');
        Route::post('/firewall/delete/{id}', [MikrotikController::class, 'deleteFirewall'])->name('mikrotik.delete-firewall');
        Route::get('/firewall/getData', [MikrotikController::class, 'getFirewallMasterData'])->name('mikrotik.firewall-master-data'); 
        
        // Firewall List
        Route::get('/firewall/{routerId}', [MikrotikController::class, 'getFirewallList'])->name('mikrotik.firewalllist');
        Route::post('/firewall/change-firewall/{routerId}', [MikrotikController::class, 'setFirewallList'])->name('mikrotik.change-firewall');
        Route::post('/firewall/edit-firewall/{routerId}/{ip}', [MikrotikController::class, 'editFirewallList'])->name('mikrotik.edit-firewall');
        Route::post('/firewall/remove/{routerId}/{id}', [MikrotikController::class, 'removeFirewallList'])->name('mikrotik.remove-firewall');
        Route::get('/firewall/get-firewall-options/{routerid}', [MikrotikController::class, 'getFirewallOptions'])->name('mikrotik.firewall-list');
        Route::get('/firewall-master/getData', [MikrotikController::class, 'getFirewallData'])->name('mikrotik.firewall-data'); 

        // PPP List
        Route::get('/l2tp/{routerId}', [MikrotikController::class, 'getL2TP'])->name('mikrotik.l2tp');    
        Route::get('/l2tps/{routerId}', [MikrotikController::class, 'getL2TPData'])->name('mikrotik.l2tp-data');    

        // PPP List
        Route::get('/ppp/{routerId}', [MikrotikController::class, 'getPPP'])->name('mikrotik.ppp');    
        Route::get('/ppps/{routerId}', [MikrotikController::class, 'getPPPSecretsData'])->name('mikrotik.ppp-data');    

        // get statistics data 
        Route::get('/usage-stats/getData/{routerId}',[MikrotikController::class, 'getUsageStatsData'])->name('mikrotik.usage-stats.data');
        // display the page
        Route::get('/usage-stats/{routerId}',[MikrotikController::class, 'getUsageStats'])->name('mikrotik.usage-stats');
    });

    // Mikrotik API
    Route::prefix('support')->middleware(['auth:sanctum', 'verified'])->group(function () {
        // Routes accessible by all authenticated users (ADMIN, MANAGER, USER)
        Route::get('/tickets/my', [SupportController::class, 'myTicketsList'])->name('support.tickets.my');
        Route::get('/tickets/my/getData', [SupportController::class, 'getMyTicketsData'])->name('support.tickets.myDatas');
        Route::get('/tickets/create', [SupportController::class, 'creatTicket'])->name('support.tickets.create');
        Route::post('/tickets/store', [SupportController::class, 'store'])->name('support.tickets.store');
        Route::get('/tickets/view/{id}', [SupportController::class, 'viewTicket'])
            ->where('id', '.*')
            ->name('support.tickets.view');

        // Routes accessible by ADMIN and MANAGER only
        Route::middleware(['check.role:admin,manager'])->group(function () {
            Route::get('/tickets', [SupportController::class, 'allTicketsList'])->name('support.tickets.list');
            Route::get('/tickets/getData', [SupportController::class, 'getAllTicketsData'])->name('support.tickets.allDatas');
            Route::get('/tickets/assigned', [SupportController::class, 'assignedTicketsList'])->name('support.tickets.assigned');
            Route::get('/tickets/assigned/getData', [SupportController::class, 'getAssignedTicketsData'])->name('support.tickets.assignedDatas');
            Route::get('/tickets/view-admin/{id}', [SupportController::class, 'viewTicketAdmin'])
                ->where('id', '.*')
                ->name('support.tickets.view-admin');
            Route::put('/tickets/update/{ticketId}', [SupportController::class, 'updateTicket'])->name('support.tickets.update');
        });

        // Routes accessible by ADMIN only
        Route::middleware(['check.role:admin'])->group(function () {
            Route::delete('/tickets/delete/{ticketId}', [SupportController::class, 'deleteTicket'])->name('support.tickets.delete');
            Route::put('/tickets/close/{id}', [SupportController::class, 'closeTicket'])
                ->where('id', '.*')
                ->name('support.tickets.close');
        });

        Route::post('/tickets/reply', [SupportController::class, 'submitReply'])
            ->name('support.tickets.reply');
        Route::get('/tickets/attachment/{attachmentId}', [SupportController::class, 'downloadAttachment'])
            ->name('support.tickets.download.reply.attachment');
    });

    // SalesOrders
    Route::prefix('sales')->group(function () {
        Route::get('/sales-order', [SalesOrderController::class, 'index'])->name('sales-order');
        Route::get('/sales-order/getdata', [SalesOrderController::class, 'getData'])->name('sales-order.getdata');
        Route::get('/sales-order/form', [SalesOrderController::class, 'form'])->name('sales-order.form');
        Route::post('/sales-order/create', [SalesOrderController::class, 'create'])->name('sales-order.create');
        Route::get('/sales-order/getcustomer/customerId', [SalesOrderController::class, 'getCustomer'])->name('create.getcustomer');
        Route::get('/sales-order/getproduct', [SalesOrderController::class, 'getProduct'])->name('create.getproduct');
        Route::get('/sales-order/getdetail/{salesId}', [SalesOrderController::class, 'getDetail'])->name('sales-order.getdetail');
        Route::post('/sales-order/update/{salesId}', [SalesOrderController::class, 'updateSo'])->name('sales-order.updateso');
        Route::get('/sales-order/print/{salesId}', [SalesOrderController::class, 'print'])->name('sales-order.print');
    });
    

    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/getdata', [DashboardController::class, 'getData'])->name('dashboard.sales');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/dashboard/fintech', [DashboardController::class, 'fintech'])->name('fintech');
    Route::get('/ecommerce/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/ecommerce/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/ecommerce/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/ecommerce/shop', function () {
        return view('pages/ecommerce/shop');
    })->name('shop');
    Route::get('/ecommerce/shop-2', function () {
        return view('pages/ecommerce/shop-2');
    })->name('shop-2');
    Route::get('/ecommerce/product', function () {
        return view('pages/ecommerce/product');
    })->name('product');
    Route::get('/ecommerce/cart', function () {
        return view('pages/ecommerce/cart');
    })->name('cart');
    Route::get('/ecommerce/cart-2', function () {
        return view('pages/ecommerce/cart-2');
    })->name('cart-2');
    Route::get('/ecommerce/cart-3', function () {
        return view('pages/ecommerce/cart-3');
    })->name('cart-3');
    Route::get('/ecommerce/pay', function () {
        return view('pages/ecommerce/pay');
    })->name('pay');
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
    Route::get('/community/users-tabs', [MemberController::class, 'indexTabs'])->name('users-tabs');
    Route::get('/community/users-tiles', [MemberController::class, 'indexTiles'])->name('users-tiles');
    Route::get('/community/profile', function () {
        return view('pages/community/profile');
    })->name('profile');
    Route::get('/community/feed', function () {
        return view('pages/community/feed');
    })->name('feed');
    Route::get('/community/forum', function () {
        return view('pages/community/forum');
    })->name('forum');
    Route::get('/community/forum-post', function () {
        return view('pages/community/forum-post');
    })->name('forum-post');
    Route::get('/community/meetups', function () {
        return view('pages/community/meetups');
    })->name('meetups');
    Route::get('/community/meetups-post', function () {
        return view('pages/community/meetups-post');
    })->name('meetups-post');
    Route::get('/finance/cards', function () {
        return view('pages/finance/credit-cards');
    })->name('credit-cards');
    Route::get('/finance/transactions', [TransactionController::class, 'index01'])->name('transactions');
    Route::get('/finance/transaction-details', [TransactionController::class, 'index02'])->name('transaction-details');
    Route::get('/job/job-listing', [JobController::class, 'index'])->name('job-listing');
    Route::get('/job/job-post', function () {
        return view('pages/job/job-post');
    })->name('job-post');
    Route::get('/job/company-profile', function () {
        return view('pages/job/company-profile');
    })->name('company-profile');
    Route::get('/messages', function () {
        return view('pages/messages');
    })->name('messages');
    Route::get('/inbox', function () {
        return view('pages/inbox');
    })->name('inbox');
    Route::get('/settings/account', function () {
        return view('pages/settings/account');
    })->name('account');
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');
    Route::get('/settings/apps', function () {
        return view('pages/settings/apps');
    })->name('apps');
    Route::get('/settings/plans', function () {
        return view('pages/settings/plans');
    })->name('plans');
    Route::get('/settings/billing', function () {
        return view('pages/settings/billing');
    })->name('billing');
    Route::get('/settings/feedback', function () {
        return view('pages/settings/feedback');
    })->name('feedback');
    Route::get('/utility/changelog', function () {
        return view('pages/utility/changelog');
    })->name('changelog');
    Route::get('/utility/roadmap', function () {
        return view('pages/utility/roadmap');
    })->name('roadmap');
    Route::get('/utility/faqs', function () {
        return view('pages/utility/faqs');
    })->name('faqs');
    Route::get('/utility/empty-state', function () {
        return view('pages/utility/empty-state');
    })->name('empty-state');
    Route::get('/utility/404', function () {
        return view('pages/utility/404');
    })->name('404');
    Route::get('/utility/knowledge-base', function () {
        return view('pages/utility/knowledge-base');
    })->name('knowledge-base');
    Route::get('/onboarding-01', function () {
        return view('pages/onboarding-01');
    })->name('onboarding-01');
    Route::get('/onboarding-02', function () {
        return view('pages/onboarding-02');
    })->name('onboarding-02');
    Route::get('/onboarding-03', function () {
        return view('pages/onboarding-03');
    })->name('onboarding-03');
    Route::get('/onboarding-04', function () {
        return view('pages/onboarding-04');
    })->name('onboarding-04');
    Route::get('/component/button', function () {
        return view('pages/component/button-page');
    })->name('button-page');
    Route::get('/component/form', function () {
        return view('pages/component/form-page');
    })->name('form-page');
    Route::get('/component/dropdown', function () {
        return view('pages/component/dropdown-page');
    })->name('dropdown-page');
    Route::get('/component/alert', function () {
        return view('pages/component/alert-page');
    })->name('alert-page');
    Route::get('/component/modal', function () {
        return view('pages/component/modal-page');
    })->name('modal-page');
    Route::get('/component/pagination', function () {
        return view('pages/component/pagination-page');
    })->name('pagination-page');
    Route::get('/component/tabs', function () {
        return view('pages/component/tabs-page');
    })->name('tabs-page');
    Route::get('/component/breadcrumb', function () {
        return view('pages/component/breadcrumb-page');
    })->name('breadcrumb-page');
    Route::get('/component/badge', function () {
        return view('pages/component/badge-page');
    })->name('badge-page');
    Route::get('/component/avatar', function () {
        return view('pages/component/avatar-page');
    })->name('avatar-page');
    Route::get('/component/tooltip', function () {
        return view('pages/component/tooltip-page');
    })->name('tooltip-page');
    Route::get('/component/accordion', function () {
        return view('pages/component/accordion-page');
    })->name('accordion-page');
    Route::get('/component/icons', function () {
        return view('pages/component/icons-page');
    })->name('icons-page');
    Route::fallback(function () {
        return view('pages/utility/404');
    });
});
