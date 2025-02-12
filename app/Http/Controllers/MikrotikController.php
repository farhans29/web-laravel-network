<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikApiService;
use App\Models\Router;
use Yajra\DataTables\Facades\DataTables;

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
}
