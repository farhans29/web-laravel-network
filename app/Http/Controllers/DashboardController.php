<?php

    namespace App\Http\Controllers;

    use App\Models\Router;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use App\Models\DataFeed;
    use App\Models\DailyTraffic;
    use App\Services\MikrotikApiService;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Yajra\DataTables\Facades\DataTables;
    use App\Helpers\Helper;

    class DashboardController extends Controller
    {

        /**
         * Displays the dashboard screen
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
         */
        public function index(Request $request)
        {
            $dataFeed = new DataFeed();

            $userId = Auth::user()->id;
            $groupId = Auth::user()->idusergrouping;
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime($today . "+1 days"));
            $tomorrow5days = date('Y-m-d', strtotime($today . "+5 days"));

            
            $mikrotikService = new MikrotikApiService();

            $dataRouter = Router::where('idusergrouping', $groupId)->get();
            $yesterday = Carbon::yesterday()->toDateString();
            foreach ($dataRouter as $router) {
                    $client = $mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
                    $router->count = $mikrotikService->getBoundLease($client);

                    $traffic = DailyTraffic::where('idrouter', $router->idrouter)
                        ->whereDate('datetime', $yesterday)
                        ->selectRaw('SUM(tx_bytes) as total_tx, SUM(rx_bytes) as total_rx')
                        ->first();

                    $router->tx = $this->formatBytes($traffic->total_tx);
                    $router->rx = $this->formatBytes($traffic->total_rx);
                }

            // dd($dataRouter);

            return view('pages/dashboard/dashboard', compact('dataFeed', 'dataRouter'));
        }

        /**
         * Displays the analytics screen
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
         */
        public function analytics()
        {
            return view('pages/dashboard/analytics');
        }

        /**
         * Displays the fintech screen
         *
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
         */
        public function fintech()
        {
            return view('pages/dashboard/fintech');
        }

        public function getData(Request $request)
        {
            $userRole = Auth::user()->role;
            $salesId = Auth::user()->sales_id;
            $filterYear = $request->input('year');
            $yearNow = date('Y');
            $year = $filterYear ?? $yearNow;

            $dataSalesGlobalQuery = DB::table('sales_orders')
                ->selectRaw("
                    YEAR(delivery_date) AS year,
                    MONTHNAME(delivery_date) AS month,
                    MONTH(delivery_date) AS month_number,
                    SUM(total) AS net_sales_total,
                    COUNT(id) AS invoice_count,
                    COUNT(DISTINCT customer_id) AS customer_count
                ")
                ->whereRaw("YEAR(delivery_date) = $year");

            if ($userRole == '200' || $userRole == '201' || $userRole == '202' || $userRole == '203'){
                $dataSalesGlobalQuery->where('created_by', $salesId);
            }

            // data for chart
            $arrayLabel = [];
            $arrayData = [];

            $dataSalesGlobal = $dataSalesGlobalQuery
                ->groupByRaw("YEAR(delivery_date), MONTHNAME(delivery_date)")
                ->orderByRaw("MONTH(delivery_date)")
                ->get();

            foreach ($dataSalesGlobal as $key => $value) {
                $label = substr($value->month, 0, 3) . ' ' . $value->year;
                array_push($arrayLabel, $label);
                array_push($arrayData, $value->net_sales_total * 1);
            }

            return response()->json([
                'labels' => $arrayLabel,
                'data' => $arrayData
            ]);
        }

        function formatBytes($bytes, $precision = 2) {
            if ($bytes <= 0) return '0 B'; // Handle zero and negative values properly
        
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $factor = max(0, floor(log($bytes, 1024))); // Avoid negative index
        
            return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
        }

    }
