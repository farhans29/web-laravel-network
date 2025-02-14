<?php

    namespace App\Http\Controllers;

    use App\Models\Router;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use App\Models\DataFeed;
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

            $dataRouter = Router::where('idusergrouping', $groupId)->get();

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

    }
