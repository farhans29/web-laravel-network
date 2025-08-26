<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikApiService;

use App\Models\Router;
use App\Models\DhcpClient;
use App\Models\Firewall;
use App\Models\FirewallList;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Fetch interfaces
        $interfaces = collect($this->mikrotikService->getInterfaces($client));
        dd($interfaces);

        return view('pages/mikrotik/interfaces', compact('router', 'interfaces'));
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
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
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

        $firewalls = DB::table('m_router_firewall')
            ->where('idrouter', $routerId)
            ->orWhere('idrouter', '0')
            ->orderBy('firewall_name', 'asc')
            ->get();

        // $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        // }

        // $devices = $this->mikrotikService->getDhcpLeases($client);
        // $devices = $this->mikrotikService->getFirewallList($client);
        // dd($devices);

        $this->insertConnectedDevicesDB($routerId);
        $this->insertFirewallListDB($routerId);

        return view('pages/mikrotik/devices-list', compact('router', 'firewalls'));
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
                        't_dhcp_list.comment',
                        't_firewall_addresslist.id_firewall',
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
                            ->addColumn('action', function ($row) use ($routerId) {
                                if ($row->dynamic === 'false') {
                                    return '
                                            <div class="flex flex-row justify-center space-x-2">
                                                <button class="btn btn-sm btn-delete text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                                    data-id="' . $row->id_dhcp . '"
                                                    data-routerid="' . $routerId . '">
                                                    🖥️ <span class="ml-2">Delete Static IP</span>
                                                </button>

                                                <div x-data="{ modalOpen: false }">
                                                    <button class="btn btn-sm btn-firewall text-sm text-white flex items-center justify-center px-4 py-2 ml-1"
                                                        style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                                        @click.prevent="modalOpen = true" aria-controls="scrollbar-modal"
                                                            data-iddhcp="' . $row->id_dhcp . '"
                                                            data-idfirewall="' . $row->id_firewall . '"
                                                            data-routerid="' . $routerId . '"
                                                            data-ip="' . $row->address . '"
                                                            data-mac="' . $row->mac_address . '"
                                                            data-name="' . $row->host_name . '"
                                                            data-status="' . $row->status . '">
                                                    🌏 <span class="ml-2">Firewall</span>
                                                    </button>
                            
                                                    <!-- Modal backdrop -->
                                                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                                                        x-cloak>
                                                    </div>
                                                    <!-- Modal dialog -->
                                                    <div id="feedback-modal"
                                                        class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                        role="dialog" aria-modal="true" x-show="modalOpen"
                                                        x-transition:enter="transition ease-in-out duration-200"
                                                        x-transition:enter-start="opacity-0 translate-y-4"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in-out duration-200"
                                                        x-transition:leave-start="opacity-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                                            @keydown.escape.window="modalOpen = false">
                                                            <!-- Modal header -->
                                                            <div class="px-5 py-3 border-b border-slate-200">
                                                                <div class="flex justify-between items-center">
                                                                    <div class="font-semibold text-slate-800 text-sm">Firewall Settings</div>
                                                                    <button class="text-slate-400 hover:text-slate-500"
                                                                        @click="modalOpen = false">
                                                                        <div class="sr-only">Close</div>
                                                                        <svg class="w-4 h-4 fill-current">
                                                                            <path
                                                                                d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="modal-content text-xs">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            ';
                                } else {
                                    return '
                                            <div class="flex flex-row justify-center space-x-2">                                                
                                                <div x-data="{ modalOpen: false }">
                                                    <button class="btn btn-sm btn-modal text-sm text-white flex items-center justify-center px-4 py-2 ml-1"
                                                        style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                                        @click.prevent="modalOpen = true" aria-controls="scrollbar-modal"
                                                            data-id="' . $row->id_dhcp . '"
                                                            data-routerid="' . $routerId . '">
                                                    🖥️ <span class="ml-2">Make Static IP</span>
                                                    </button>
                            
                                                    <!-- Modal backdrop -->
                                                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                                                        x-cloak></div>
                                                    <!-- Modal dialog -->
                                                    <div id="feedback-modal"
                                                        class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                        role="dialog" aria-modal="true" x-show="modalOpen"
                                                        x-transition:enter="transition ease-in-out duration-200"
                                                        x-transition:enter-start="opacity-0 translate-y-4"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in-out duration-200"
                                                        x-transition:leave-start="opacity-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                                            @keydown.escape.window="modalOpen = false">
                                                            <!-- Modal header -->
                                                            <div class="px-5 py-3 border-b border-slate-200">
                                                                <div class="flex justify-between items-center">
                                                                    <div class="font-semibold text-slate-800 text-sm">Set to Static</div>
                                                                    <button class="text-slate-400 hover:text-slate-500"
                                                                        @click="modalOpen = false">
                                                                        <div class="sr-only">Close</div>
                                                                        <svg class="w-4 h-4 fill-current">
                                                                            <path
                                                                                d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="modal-content text-xs">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button class="btn btn-sm btn-change text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;">
                                                    🌏 <span class="ml-2">Firewall</span>
                                                </button>
                                            </div>
                                            ';
                                }
                            })
                            
                            ->rawColumns(['action'])
                            ->make();
                    }

    }

    public function getFirewall($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $firewalls = DB::table('m_router_firewall')
            ->where('idrouter', $routerId)
            ->orWhere('idrouter', '0')
            ->orderBy('firewall_name', 'asc')
            ->get();

        // $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        // }

        // $devices = $this->mikrotikService->getDhcpLeases($client);
        // $devices = $this->mikrotikService->getFirewallList($client);
        // dd($devices);
        $this->insertFirewallListDB($routerId);

        return view('pages/mikrotik/firewall-list', compact('router', 'firewalls'));
    }
    
    public function getFirewallMaster($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        // $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password);

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        // }

        // $devices = $this->mikrotikService->getDhcpLeases($client);
        // $devices = $this->mikrotikService->getFirewallList($client);
        // dd($devices);

        return view('pages/mikrotik/firewall-master-list', compact('router'));
    }

    public function getFirewallMasterData(Request $request)
    {
        $routerId = $request->routerId;

        $results = Firewall::where('idrouter', $routerId)
            ->orWhere('idrouter', '0')
            ->get();

        if ($request->ajax()) {
            return DataTables::of($results)

            //Add Action Column
            ->addColumn('action', function ($row) use ($routerId) {
                return '
                        <div class="flex flex-row justify-center space-x-2">                               
                            <div x-data="{ modalOpen: false }">
                                <button class="btn btn-sm btn-firewall text-sm text-white flex items-center justify-center px-4 py-2 ml-1"
                                    style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                    @click.prevent="modalOpen = true" aria-controls="scrollbar-modal"
                                        data-id="' . $row->idrec . '"
                                        data-firewallname="' . $row->firewall_name . '"
                                    <span class="ml-2">Edit</span>
                                </button>
        
                                <!-- Modal backdrop -->
                                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                                    x-cloak>
                                </div>
                                <!-- Modal dialog -->
                                <div id="feedback-modal"
                                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                    role="dialog" aria-modal="true" x-show="modalOpen"
                                    x-transition:enter="transition ease-in-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-4"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in-out duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                        @keydown.escape.window="modalOpen = false">
                                        <!-- Modal header -->
                                        <div class="px-5 py-3 border-b border-slate-200">
                                            <div class="flex justify-between items-center">
                                                <div class="font-semibold text-slate-800 text-sm">Firewall Settings</div>
                                                <button class="text-slate-400 hover:text-slate-500"
                                                    @click="modalOpen = false">
                                                    <div class="sr-only">Close</div>
                                                    <svg class="w-4 h-4 fill-current">
                                                        <path
                                                            d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Modal content -->
                                        <div class="modal-content text-xs">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-sm btn-delete text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                    data-id="' . $row->idrec . '"
                                    data-routerid="' . $routerId . '"
                                    data-firewallname="' . $row->firewall_name . '"
                                <span class="ml-2">Delete</span>
                            </button>
                        </div>
                        ';
            })
            
            ->rawColumns(['action'])
            ->make(true);
        }

        // Handle non-AJAX requests
        return response()->json($results);
    }

    public function getFirewallData(Request $request)
    {
        $routerId = $request->routerId;

        $results = FirewallList::where('idrouter', $routerId)
            ->get();

        if ($request->ajax()) {
            return DataTables::of($results)

            //Add Action Column
            ->addColumn('action', function ($row) use ($routerId) {
                return '
                        <div class="flex flex-row justify-center space-x-2">                               
                            <div x-data="{ modalOpen: false }">
                                <button class="btn btn-sm btn-firewall text-sm text-white flex items-center justify-center px-4 py-2 ml-1"
                                    style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                    @click.prevent="modalOpen = true" aria-controls="scrollbar-modal"
                                        data-iddhcp="' . $row->id_dhcp . '"
                                        data-idfirewall="' . $row->id_firewall . '"
                                        data-routerid="' . $routerId . '"
                                        data-ip="' . $row->address . '"
                                        data-status="' . $row->status . '">
                                    <span class="ml-2">Edit</span>
                                </button>
        
                                <!-- Modal backdrop -->
                                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                                    x-cloak>
                                </div>
                                <!-- Modal dialog -->
                                <div id="feedback-modal"
                                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                    role="dialog" aria-modal="true" x-show="modalOpen"
                                    x-transition:enter="transition ease-in-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-4"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in-out duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                        @keydown.escape.window="modalOpen = false">
                                        <!-- Modal header -->
                                        <div class="px-5 py-3 border-b border-slate-200">
                                            <div class="flex justify-between items-center">
                                                <div class="font-semibold text-slate-800 text-sm">Firewall Settings</div>
                                                <button class="text-slate-400 hover:text-slate-500"
                                                    @click="modalOpen = false">
                                                    <div class="sr-only">Close</div>
                                                    <svg class="w-4 h-4 fill-current">
                                                        <path
                                                            d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Modal content -->
                                        <div class="modal-content text-xs">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-sm btn-delete text-sm text-white flex items-center justify-center px-4 py-2 ml-1" 
                                style="background-color: rgb(2 132 199); transition: background-color 0.3s ease-in-out;"
                                    data-id="' . $row->id_firewall . '"
                                    data-ip="' . $row->address . '"
                                    data-routerid="' . $routerId . '"
                                <span class="ml-2">Delete</span>
                            </button>
                        </div>
                        ';
            })
            
            ->rawColumns(['action'])
            ->make(true);
        }

        // Handle non-AJAX requests
        return response()->json($results);
    }

    public function getConnectedDevice($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);

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

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $devices = $this->mikrotikService->getFirewallList($client);
        // dd($devices);

        return view('pages/mikrotik/interfaces', compact('devices', 'router'));
    }

    public function getMonitor($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();
        $interface = 'ether4';

        $router1 = ['routerId' => 'EC190F8BE928', 'name' => 'Genco - CF'];
        $router2 = ['routerId' => 'HEP08GP7NN0', 'name' => 'Fasdex - CD'];

        // $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);

        // if (!$client) {
        //     return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        // }

        // $traffic = $this->mikrotikService->getTrafficMonitor($client);
        // dd($devices);

        return view('pages/mikrotik/monitoring', compact('router', 'interface', 'router1', 'router2'));
    }

    public function getTraffic($routerId, $interface)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);

        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik router.'], 500);
        }

        return $this->mikrotikService->getTraffic($client, $interface);
        // $traffic = $this->mikrotikService->getTraffic($client, $interface);
        // dd($traffic);

        // return $traffic;        
        // return view('pages/mikrotik/interfaces', compact('router'));
    }

    public function getTrafficData($routerId)
    {
        $router = Router::where('idrouter', $routerId)->first();

        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);

        if (!$client) {
            return redirect()->back()->with('error', 'Failed to connect to MikroTik router.');
        }

        $interface = 'bridge';
        $traffic = $this->mikrotikService->getTraffic($client, $interface);
        dd($traffic);
        
        return view('pages/mikrotik/interfaces', compact('router'));
    }

    public function insertConnectedDevicesDB($routerId)
    {
        // Retrieve router details from the database
        $router = Router::where('idrouter', $routerId)->first();
    
        // Attempt to connect to the MikroTik router
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
    
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
                    'comment'  => $device['comment'] ?? null,
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
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
    
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
                    'id_firewall'   => $device['.id'],
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

    //Usage
    public function getUsageStats($routerId) 
    {
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);

        return view('pages/mikrotik/usage-stats', compact('router'));
    }

    public function getUsageStatsData(Request $request, $routerId)
    {
        // Query usage statistics from `t_traffic_logs_daily`
        $dataStats = DB::table('t_traffic_logs_daily')
            ->selectRaw("
                idrouter,
                int_type,
                SUM(tx_bytes) as Upload,
                SUM(rx_bytes) as Download,
                DATE(datetime) as date
            ")
            ->where('idrouter', $routerId)
            ->groupBy('date', 'int_type') // Group by date and interface type
            ->orderBy('date', 'asc') // Sort by date first
            ->orderBy('int_type', 'asc') // Then sort by interface type
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

    public function getUsageStatsJson($routerId)
    {

    }
    
    //L2TP
    public function getL2TP($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        return view('pages/mikrotik/l2tp-list', compact('router'));
    }

    public function getL2TPData($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Fetch interfaces
        $interfaces = collect($this->mikrotikService->getPPP($client));
        dd($interfaces);

        return view('pages/mikrotik/interfaces', compact('router'));
    }

    public function getL2TPDataJson(Request $request)
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
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik router.'], 500);
        }

        // Fetch interfaces
        $ppp = $this->mikrotikService->getPPP($client);

        return response()->json([
            // 'success' => true,
            // 'data' => $ppp,
            $ppp
        ], 200);
    }
    
    //PPP
    public function getPPP($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        return view('pages/mikrotik/l2tp-user-list', compact('router'));
    }

    public function getPPPSecretsData($routerId)
    {        
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Fetch interfaces
        $interfaces = collect($this->mikrotikService->getPPPSecrets($client));
        dd($interfaces);

        return view('pages/mikrotik/interfaces', compact('router'));
    }

    public function getPPPSecretsDataJson(Request $request)
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
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik router.'], 500);
        }

        // Fetch interfaces
        $ppp = $this->mikrotikService->getPPPSecrets($client);

        return response()->json([
            // 'success' => true,
            // 'data' => $ppp,
            $ppp
        ], 200);
    }
    
    public function setStatic(Request $request, $leaseId, $routerId)
    {

        $comment = $request->input('username') . " - " . $request->input('department') . " - " . $request->input('deviceName');

        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Run Command
        $results = $this->mikrotikService->makeStatic($client, $leaseId, $comment);
        // dd($results);
        // \Log::info("MikroTik Response:", $results);

        if ($results === "1") {                        
            alert()->success('Success', 'IP is now static');
            return to_route('mikrotik.devices', ['routerId' => $routerId]);                   
        } else {            
            alert()->error('Error', 'IP is not dynamic or not found');
            return to_route('mikrotik.devices', ['routerId' => $routerId]);         
        }
    }

    public function deleteStatic($leaseId, $routerId)
    {
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Run Command
        $results = $this->mikrotikService->removeStatic($client, $leaseId);

        if ($results === "1") {                        
            alert()->success('Success', 'IP is now dynamic');
            return to_route('mikrotik.devices', ['routerId' => $routerId]);                   
        } else {            
            alert()->error('Error', 'IP is not static or not found');
            return to_route('mikrotik.devices', ['routerId' => $routerId]);         
        }
    }

    public function getFirewallOptions($routerid)
    {
        $firewalls = DB::table('m_router_firewall')
            ->where('idrouter', $routerid)
            ->get();

        // dd($firewalls);

        return response()->json($firewalls);
    }

    public function setFirewallList(Request $request, $routerId)
    {
        $ip = $request->input('ip');
        $targetList = $request->input('firewall');
        $user = $request->input('user');
        // dd($ip, $targetList, $user);

        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Run Command
        $results = $this->mikrotikService->addOrUpdateFirewallList($client, $ip, $targetList, $user);
        // dd($results);

        // Decode the response to check its contents
        $responseData = json_decode($results->getContent(), true);

        // Check if the response contains a success message
        if (isset($responseData['message']) && str_contains($responseData['message'], 'moved')) {            
            alert()->success('Success', 'IP is now updated');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);         
        } elseif (isset($responseData['message']) && str_contains($responseData['message'], 'added')) {            
            alert()->success('Success', 'IP is added to firewall list');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);          
        } else {            
            alert()->error('Error', 'IP is not found or already on the chosen firewall list');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);
        }
    }

    public function editFirewallList(Request $request, $routerId, $ip)
    {
        // $ip = $request->input('ip');
        $targetList = $request->input('firewall');
        $user = $request->input('user');
        // dd($ip, $targetList, $user);

        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Run Command
        $results = $this->mikrotikService->addOrUpdateFirewallList($client, $ip, $targetList, $user);
        // dd($results);

        // Decode the response to check its contents
        $responseData = json_decode($results->getContent(), true);

        // Check if the response contains a success message
        if (isset($responseData['message']) && str_contains($responseData['message'], 'moved')) {            
            alert()->success('Success', 'IP is now updated');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);         
        } elseif (isset($responseData['message']) && str_contains($responseData['message'], 'added')) {            
            alert()->success('Success', 'IP is added to firewall list');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);          
        } else {            
            alert()->error('Error', 'IP is not found or already on the chosen firewall list');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);
        }
    }

    public function removeFirewallList(Request $request, $routerId, $id)
    {
        // Get router details from DB
        $router = Router::where('idrouter', $routerId)->first();
        // dd($router);
        
        if (!$router) {
            return response()->json(['error' => 'Router not found.'], 404);
        }

        // Connect to MikroTik
        $client = $this->mikrotikService->connect($router->ip, $router->login, $router->password, $router->api_port);
        if (!$client) {
            return response()->json(['error' => 'Failed to connect to MikroTik.'], 500);
        }

        // Run Command
        $results = $this->mikrotikService->removeFromFirewallList($client, $id);
        // dd($results);

        // Decode the response to check its contents
        $responseData = json_decode($results->getContent(), true);

        // Check if the response contains a success message
        if (isset($responseData['message']) && str_contains($responseData['message'], 'removed')) {            
            alert()->success('Success', 'Firewall List has been deleted');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);         
        } else {            
            alert()->error('Error', 'Something went wrong');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);
        }
    }

    public function insertIntoFirewall(Request $request, $routerId)
    {
        // Validate request
        $request->validate([
            'firewall' => 'required|string|max:255'
        ]);

        try {
            // Insert into the database
            $firewall = new Firewall();
            $firewall->idrouter = $routerId;
            $firewall->firewall_name = $request->input('firewall');
            $firewall->save();

            alert()->success('Success', 'Firewall has been created');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);
        } catch (\Exception $e) {
            alert()->error('Error', 'Please try again or contact admin!');
            return to_route('mikrotik.firewall-page', ['routerId' => $routerId]);
        }
    }

    public function updateFirewall(Request $request, $idrec)
    {
        // Validate the request
        $request->validate([
            'firewall_name' => 'required|string|max:255',
        ]);

        // Find the firewall record by ID
        $firewall = Firewall::find($idrec);

        if (!$firewall) {
            return response()->json(['error' => 'Firewall entry not found.'], 404);
        }

        // Update firewall entry
        $firewall->firewall_name = $request->input('firewall_name');
        $firewall->save();

        return response()->json(['message' => 'Firewall entry updated successfully.']);
    }

    public function deleteFirewall($idrec)
    {
        // Find the firewall record by ID
        $firewall = Firewall::find($idrec);
    
        if (!$firewall) {
            return response()->json(['error' => 'Firewall entry not found.'], 404);
        }
    
        // Delete the record
        $firewall->delete();
    
        return response()->json(['message' => 'Firewall entry deleted successfully.']);
    }

}
