<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikApiService;

use Illuminate\Http\Request;
use App\Models\Router;
use App\Models\DhcpClient;
use App\Models\FirewallList;
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

    public function getInterface($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);

        return view('pages/mikrotik/interfaces', compact('router'));
    }

    public function getClientDevicesData(Request $request)
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

        // $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        // }

        // $devices = $this->mikrotikService->getDhcpLeases($client);
        // $devices = $this->mikrotikService->getFirewallList($client);
        // dd($devices);

        $this->insertConnectedDevicesDB($routerId);
        $this->insertFirewallListDB($routerId);

        return view('pages/mikrotik/devices-list', compact('router'));
    }
    
    public function getConnectedDevicesData(Request $request)
    {
        $routerId = $request->routerId;
        
        $results = DhcpClient::leftJoin('t_firewall_addresslist', 't_firewall_addresslist.address', '=', 't_dhcp_list.address')
                    ->select(
                        't_dhcp_list.id_dhcp',
                        't_dhcp_list.address as address',
                        't_dhcp_list.mac_address',
                        't_dhcp_list.host_name',
                        't_dhcp_list.server',
                        't_dhcp_list.dynamic',
                        't_firewall_addresslist.list as status'
                    )
                    ->where('t_dhcp_list.idrouter', '=', $routerId)
                    ->get();

                    if ($request->ajax()) {
                        return DataTables::of($results)
                            // Modify 'dynamic' column: Convert true/false to 'True'/'False'
                            ->editColumn('dynamic', function ($row) {
                                return strtolower($row->dynamic) === 'true' ? 'True' : 'False';
                            })
                    
                            // Modify 'status' column: Custom mapping
                            ->editColumn('status', function ($row) {
                                if ($row->status === 'Open Internet') {
                                    return 'Internet';
                                } elseif ($row->status === 'Open Email') {
                                    return 'Email';
                                } else {
                                    return 'Firewalled';
                                }
                            })

                            //Add Action Column
                            ->addColumn('action', function ($row) {
                                if ($row->dynamic === 'false') {
                                    return '
                                            <div class="flex flex-row justify-center space-x-2">
                                                <a href="/ga/rab-approval/list/printedenforced/{{ $row->id_dhcp }}" 
                                                target="_blank" 
                                                class="btn btn-sm text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;">
                                                    🖥️ <span class="ml-2">Delete Static IP</span>
                                                </a>
                                                
                                                <a href="/ga/rab-approval/list/printedenforced/{{ $row->id_dhcp }}" 
                                                target="_blank" 
                                                class="btn btn-sm text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;">
                                                    🌏 <span class="ml-2">Change Internet Status</span>
                                                </a>
                                            </div>
                                            ';
                                } else {
                                    return '
                                            <div class="flex flex-row justify-center space-x-2">
                                                <a href="/ga/rab-approval/list/printedenforced/{{ $row->id_dhcp }}" 
                                                target="_blank" 
                                                class="btn btn-sm text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;">
                                                    🖥️ <span class="ml-2">Make Static IP</span>
                                                </a>
                                                
                                                <a href="/ga/rab-approval/list/printedenforced/{{ $row->id_dhcp }}" 
                                                target="_blank" 
                                                class="btn btn-sm text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;">
                                                    🌏 <span class="ml-2">Change Internet Status</span>
                                                </a>
                                            </div>
                                            ';
                                }
                            })
                            
                            ->rawColumns(['action'])
                            ->make();
                    }

    }

    public function getConnectedDevice($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $devices = $this->mikrotikService->getDhcpLeases($client);
        dd($devices);

        return view('pages/mikrotik/interfaces', compact('devices', 'router'));
    }

    public function getFirewallList($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $devices = $this->mikrotikService->getFirewallList($client);
        dd($devices);

        return view('pages/mikrotik/interfaces', compact('devices', 'router'));
    }

    public function insertConnectedDevicesDB($routerId)
    {
        // Retrieve router details from the database
        $router = Router::where('idrouter', $routerId)->first();
    
        // Attempt to connect to the MikroTik router
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);
    
        // If the connection fails, return an error message
        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }
    
        // Fetch the list of connected devices from the MikroTik router
        $devices = $this->mikrotikService->getDhcpLeases($client);
    
        // Begin the database transaction
        DB::beginTransaction();
    
        try {
            // Delete existing records for this router ID
            DB::table('t_dhcp_list')->where('idrouter', $routerId)->delete();
    
            // Insert new device records
            foreach ($devices as $device) {
                DB::table('t_dhcp_list')->insert([
                    'idrouter'   => $routerId,  // Track which router the data belongs to
                    'id_dhcp'   => $device['.id'],
                    'mac_address' => $device['mac-address'] ?? null,
                    'address'  => $device['address'] ?? null,
                    'host_name'   => $device['host-name'] ?? null,
                    'server'     => $device['server'] ?? null,
                    'status'    => $device['status'] ?? null, // Timestamp for tracking when the record was added
                    'dynamic'     => $device['dynamic'] ?? null,
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            // return redirect()->back()->with('success', 'Connected devices inserted successfully.');
        } catch (\Exception $e) {
            // Rollback in case of an error
            DB::rollBack();
            // return redirect()->back()->with('error', 'Failed to insert devices: ' . $e->getMessage());
        }
    }

    public function insertFirewallListDB($routerId)
    {
        // Retrieve router details from the database
        $router = Router::where('idrouter', $routerId)->first();
    
        // Attempt to connect to the MikroTik router
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);
    
        // If the connection fails, return an error message
        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }
    
        // Fetch the list of connected devices from the MikroTik router
        $devices = $this->mikrotikService->getFirewallList($client);
    
        // Begin the database transaction
        DB::beginTransaction();
    
        try {
            // Delete existing records for this router ID
            DB::table('t_firewall_addresslist')->where('idrouter', $routerId)->delete();
    
            // Insert new device records
            foreach ($devices as $device) {
                DB::table('t_firewall_addresslist')->insert([
                    'idrouter'   => $routerId,  // Track which router the data belongs to
                    'address'  => $device['address'] ?? null,
                    'list'   => $device['list'] ?? null,
                    'creation_time'     => $device['creation-time'] ?? null,
                    'status'    => $device['disabled'] ?? null, // Timestamp for tracking when the record was added
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            // return redirect()->back()->with('success', 'Connected devices inserted successfully.');
        } catch (\Exception $e) {
            // Rollback in case of an error
            DB::rollBack();
            // return redirect()->back()->with('error', 'Failed to insert devices: ' . $e->getMessage());
        }
    }
    
}
