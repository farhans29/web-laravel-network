<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MikrotikApiService;
use App\Models\Router;

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

        return view('pages/mikrotik/interfaces-list', compact('router'));
    }

    public function getInterfacesData(Request $request)
    {
        $routerId = $request->routerId;
        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();

        if (!$router) {
            return redirect()->back()->with('error', 'Router not found.');
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);
        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        // Fetch interfaces
        $interfaces = $this->mikrotikService->getInterfaces($client);
        // dd($interfaces);

        if ($request->ajax()) {
            return DataTables::of($interfaces)
                ->addColumn('action', function ($interfaces) {
                    return '
                    <div class="flex flex-row justify-center">
                        <a href = "/ga/rab-approval/list/view/' . $interfaces->idrec . '" class="btn btn-sm btn-modal text-sm bg-sky-500 text-white ml-1 hover:bg-sky-600"   
                        >View</a>
                        
                        <a href = "/ga/rab-approval/list/submitpage/' . $interfaces->idrec . '" class="btn btn-sm text-sm text-white ml-1" style="background-color: rgb(132 204 22); transition: background-color 0.3s ease-in-out;transition: background-color 0.3s ease-in-out;"  
                        >Submit for Review</a>                      
                        
                    </div>';
                })

                ->rawColumns(['action'])
                ->make();
        }
    }

    // public function getConnectedDevices($routerId)
    // {
    //     $router = Router::where('idrouter', $routerId)->first();

    //     $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

    //     if (!$client) {
    //         return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
    //     }

    //     $devices = $this->mikrotikService->getDhcpLeases($client);
    //     dd($devices);

    //     return view('pages/mikrotik/devices-list', compact('devices', 'router'));
    // }

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
