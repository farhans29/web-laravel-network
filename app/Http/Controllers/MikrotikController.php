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
        return view('pages/mikrotik/interfaces-list', compact('interfaces', 'router'));
    }

    public function getInterfacesData($routerId)
    {
        
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

    }

    public function getConnectedDevices($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $devices = $this->mikrotikService->getDhcpLeases($client);
        // dd($devices);

        return view('pages/mikrotik/devices-list', compact('devices', 'router'));
    }
}
