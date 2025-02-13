<?php

namespace App\Http\Controllers;

use App\Services\MikrotikApiService;

use Illuminate\Http\Request;
use App\Models\Router;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MikrotikController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikApiService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function getInterfaces($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);

        return view('pages/mikrotik/interfaces-list', compact('router'));
    }

    public function getInterfacesData(Request $request)
    {
        $routerId = $request->routerId;
        // dd($routerId);
        
        // Get router details
        $router = Router::where('idrouter', $routerId)->first();

        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Fetch interfaces
        $interfaces = collect($this->mikrotikService->getInterfaces($client));
        // dd($interfaces);

        // Debug to check structure
        if ($request->ajax()) {
            return DataTables::of($interfaces)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="flex flex-row justify-center">
                        <a href="/ga/rab-approval/list/view/' . $row['.id'] . '" class="btn btn-sm btn-modal text-sm bg-sky-500 text-white ml-1 hover:bg-sky-600">View</a>
                        
                        <a href="/ga/rab-approval/list/submitpage/' . $row['.id'] . '" class="btn btn-sm text-sm text-white ml-1" style="background-color: rgb(132 204 22); transition: background-color 0.3s ease-in-out;">Submit for Review</a>                      
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function getInterfacesDataJson(Request $request)
    {
        
        $validatedData = $request->validate([
            'idr'   => 'required|string', // Ensure it's a valid router ID
        ]);

        // Check if the secret key is provided
            // $providedKey = $request->input('key');
            // if (!$providedKey || $providedKey !== $secretKey) {
            //     return response()->json(['error' => 'Unauthorized request'], 403);
            // }

        // Extract validated data
        $idRouter  = $validatedData['idr'];
        
        $routerId = $idRouter;
        // $routerId = 2;
        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();

        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik router.'], 500);
        }

        // Fetch interfaces
        $interfaces = $this->mikrotikService->getInterfaces($client);

        return response()->json([
            // 'success' => true,
            // 'data' => $interfaces,
            $interfaces
        ], 200);
    }
    
    public function getConnectedDevices($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $devices = $this->mikrotikService->getFirewallList($client);
        dd($devices);

        return view('pages/mikrotik/devices-list', compact('devices', 'router'));
    }


    public function getUsageStats($routerId) {


        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);

        return view('pages/mikrotik/usage-stats', compact('router'));
    }

    public function getUsageStatsData(Request $request, $routerId) {
        // Get month input from request
        $filterMonth = $request->input('monthInput');
        $monthNow = date('m');
        $month = $filterMonth ?? $monthNow;

        // Query usage statistics from `t_traffic_logs_daily`
        $dataStats = DB::table('t_traffic_logs_daily')
            ->selectRaw("
                idrouter,
                int_type,
                tx_bytes as Upload,
                rx_bytes as Download,
                datetime as date
            ")
            ->where('idrouter', $routerId)
            ->whereRaw("MONTH(datetime) = ?", [$month])
            ->orderBy('datetime', 'asc') // Ensuring data is ordered by date
            ->get();

        // Prepare data for response
        $labels = [];
        $intType = [];
        $uploadData = [];
        $downloadData = [];

        foreach ($dataStats as $entry) {
            $labels[] = date('Y-m-d', strtotime($entry->date)); // Format date properly
            $intType[] = $entry->int_type;
            $uploadData[] = (int) $entry->Upload;
            $downloadData[] = (int) $entry->Download;
        }

        return response()->json([
            'labels' => $labels,
            'int_type' => $intType,
            'upload' => $uploadData,
            'download' => $downloadData
        ]);
    }


    public function getUsageStatsJson($routerId) {

    }
}
