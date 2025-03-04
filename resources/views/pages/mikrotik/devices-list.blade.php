
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Devices for {{ $router->name }}
                    @if(!isset($router) || !$router)
                        <p class="text-red-500">Error: Router not found</p>
                    @endif
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>
        
        <div id="statusContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 mb-4">
        <!-- Status boxes will be dynamically added here -->
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table id="deviceTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        {{-- <th class="text-center">*</th> --}}
                        <th class="text-center">Address</th>
                        <th class="text-center">Mac Address</th>
                        <th class="text-center">Host Name</th>
                        <th class="text-center">Notes</th>
                        <th class="text-center">Server</th>
                        <th class="text-center">DHCP</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data wil be inserted here --}}
                </tbody>
            </table>
        </div>
    </div>

    @include('components.modal-interface-image')
    @section('js-page')
    <script>
        $(document).ready(function () {
            let urlParts = window.location.pathname.split("/");
            let routerId = urlParts[urlParts.length - 1]; // Get last segment
            // $.ajax({
            //     // url: "http://127.0.0.0:8000/mikrotik/interfaces/getDataJson/?idr=1",
            //     url: "http://network.integrated-os.cloud/mikrotik/interfaces/getDataJson/?idr=1",
            //     type: "GET",
            //     data: { idr: routerId }, // Dynamically set router ID
            //     success: function (response) {
            //         let interfaces = response[0] || []; // Extract first array
            //         let tableBody = "";
            //         let statusContainer = document.getElementById("statusContainer");


            //         interfaces.forEach(function (item) {
            //             let rowColor = "background-color: #e0e0e0;"; // Default: Soft gray
            //             let statusColor = "#6c757d"; // Default gray
            //             let statusText = "Unknown";
                        
            //             if (item.running === "true" && item.disabled === "false") {
            //                 rowColor = 'background-color: #d4edda;'; // Green (Running & Enabled)
            //                 statusColor = "#28a745"; // Green
            //                 statusText = "Running ✅";
            //             } else if (item.running === "false" && item.disabled === "false") {
            //                 rowColor = 'background-color: #f5848e;'; // Red (Stopped but Enabled)
            //                 statusColor = "#dc3545"; // Red
            //                 statusText = "Stopped ❌";
            //             } else if (item.disabled === "true") {
            //                 statusColor = "#6c757d"; // Gray (Disabled)
            //                 statusText = "Disabled ⚠️";
            //             }
            //             // Append Status Box for each interface
            //             let statusBox = document.createElement("div");
            //             statusBox.className = "p-2 text-white font-semibold text-xs text-center rounded-md shadow-sm";
            //             statusBox.style.backgroundColor = statusColor;
            //             statusBox.innerHTML = `<span>${item.name}</span><br><small>${statusText}</small>`;
            //             statusContainer.appendChild(statusBox);

            //             tableBody += `
            //                 <tr style="${rowColor}">
            //                     <td>${item.name}</td>
            //                     <td>${item["mac-address"]}</td>
            //                     <td>${item["rx-byte"]}</td>
            //                     <td>${item["tx-byte"]}</td>
            //                     <td class="text-center">${item.running === "true" ? "Running" : "Stopped"}</td>
            //                     <td class="text-center">${item.disabled === "false" ? "Enabled" : "Disabled"}</td>
            //                     <td>
            //                         <button class="btn-action" data-name="${item['.id']}">
            //                             Make Static
            //                         </button>
            //                         <button class="btn-action" data-name="${item['.id']}">
            //                             View MRTG
            //                         </button>
            //                     </td>
            //                 </tr>
            //             `;
            //         });

            //     $("#interfaceTable tbody").html(tableBody);

            //     // Initialize DataTables for sorting
            //     $("#interfaceTable").DataTable({
            //         paging: true,    // Disable pagination
            //         searching: true,  // Enable search
            //         ordering: true,   // Enable column sorting
            //         // pageLength: 20,
            //         lengthMenu: [
            //             [20, 25, 50, 100], 
            //             [20, 25, 50, 100]
            //         ],
            //         layout: {
            //             topStart: 'info',
            //             bottom: 'paging',
            //             bottomStart: null,
            //             bottomEnd: null
            //         }
            //     });

            //     // Determine overall status block color
            //     let statusBlock = document.getElementById("statusBlock");
            //     if (runningCount === interfaces.length && enabledCount === interfaces.length) {
            //         statusBlock.style.backgroundColor = "#28a745"; // Green (All running & enabled)
            //         statusBlock.textContent = "All Interfaces Operational ✅";
            //     } else if (enabledCount > 0 && runningCount === 0) {
            //         statusBlock.style.backgroundColor = "#dc3545"; // Red (None running, but enabled)
            //         statusBlock.textContent = "All Interfaces Stopped ❌";
            //     } else {
            //         statusBlock.style.backgroundColor = "#6c757d"; // Gray (Mixed or unknown)
            //         statusBlock.textContent = "Partial Connection ⚠️";
            //     }

            //     $(".btn-action").on("click", function () {
            //         let interfaceName = $(this).data("name");
            //         // let interfaceName = $(this).data("name").replace(/ /g, "%20");
            //         // let routerIp = "{{ $router->ip }}"; // Blade syntax inside JavaScript
            //         // let routerPort = "{{ $router->web_port }}"; // Ensure these values are set in the controller
            //         // alert("You clicked action on: " + interfaceName + " " + routerIp + " " + routerPort);
            //         alert("You clicked on " + interfaceName);

            //         // let url = `http://${routerIp}:${routerPort}/graphs/iface/${interfaceName}`;
            //         // alert("You clicked action on: " + url);

            //         // window.open(url, "_blank", "width=800,height=600");
            //     });
            // },
            // error: function (xhr, status, error) {
            //     console.error("Error fetching data:", error);
            // }
            $('#deviceTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                stateServe: true,
                "order": [[ 0, "desc" ]],
                language: {
                    search: "Search Device Table # : "
                },
                ajax: {
                    url: "{{ route('mikrotik.devices-data') }}",
                    data:function(d){
                        d.routerId = "{{$router->idrouter}}"
                    }
                },
                columns: [
                    {
                        data: "address",
                        name: "address"
                    },
                    {
                        data: "mac_address",
                        name: "mac_address"
                    },
                    {
                        data: "host_name",
                        name: "host_name"
                    },
                    {
                        data: "comment",
                        name: "comment"
                    },
                    {
                        data: "server",
                        name: "server"
                    },
                    {
                        data: "dynamic",
                        name: "dynamic"
                    },
                    {
                        data: "status",
                        name: "status"
                    },
                    {
                        data: "action",
                        name: "action"
                    },
                ],
                columnDefs: [
                    { className: 'text-center align-middle fixed-column text-base', targets: [0, 1, 3, 4, 5, 6, 7] }, // Centered text
                    { className: 'text-left align-middle fixed-column text-base', targets: [2] }, // Left-aligned text
                ],
                lengthMenu: [[30, 50, 100, -1], [30, 50, 100, 'All']],
                autoWidth: false // Disable automatic resizing
            });

            $('#deviceTable').on("click", ".btn-modal", function () {
                const id = $(this).data("id");
                const routerid = $(this).data("routerid");

                $.ajax({
                    success: function (response) {
                        const csrf_token = $('meta[name="csrf-token"]').attr('content');

                        $(".modal-content").html(`
                            <form method="post" class="type_update" enctype="multipart/form-data" action="/mikrotik/devices/make-static/${id}/${routerid}">
                                <input type="hidden" name="_token" value="${csrf_token}"/>
                                <div class="px-5 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-slate-800"></div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm font-medium w-32 text-right" for="username">Username</label>
                                            <input id="username" name="username" type="text"
                                                class="username form-input flex-1 px-2 py-1"
                                                required />
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm font-medium w-32 text-right" for="department">Department</label>
                                            <input id="department" name="department" type="text"
                                                class="department form-input flex-1 px-2 py-1"
                                                required />
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm font-medium w-32 text-right" for="deviceName">Device Name</label>
                                            <input id="deviceName" name="deviceName" type="text"
                                                class="deviceName form-input flex-1 px-2 py-1"
                                                required />
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal footer -->
                                    <div class="px-5 py-4 border-t border-slate-200">
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button type="button"
                                                class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                                @click="modalOpen = false">Cancel</button>
                                            <button type="submit"
                                                class="btn-sm btn-update bg-indigo-500 hover:bg-indigo-600 text-white">Update</button>
                                        </div>
                                    </div>
                            </form>
                        `);
                    },
                });
            });

            $('#deviceTable').on("click", ".btn-firewall", function () {
                const id_dhcp = $(this).data("iddhcp");
                const id_firewall = $(this).data("idfirewall");
                const host_name = $(this).data("name");
                const mac_address = $(this).data("mac");
                const address = $(this).data("ip");
                const routerid = $(this).data("routerid");
                const status = $(this).data("status");

                $.ajax({
                    // url: `/mikrotik/firewall/get-firewall-options/${routerid}`, // Make sure you create this route in your backend
                    // method: "GET",
                    success: function (response) {
                        const csrf_token = $('meta[name="csrf-token"]').attr('content');

                        // // Build the options dynamically
                        // let firewallOptions = "";
                        // response.forEach(option => {
                        //     firewallOptions += `<option value="${option.id}">${option.name}</option>`;
                        // });

                        $(".modal-content").html(`
                            <form method="post" class="type_update" enctype="multipart/form-data" action="/mikrotik/firewall/change-firewall/${routerid}">
                                <input type="hidden" name="_token" value="${csrf_token}"/>

                                <div class="px-5 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-slate-800"></div>
                                    </div>

                                    <div class="space-y-3">
                                        <!-- IP Address -->
                                        <div class="grid grid-cols-3 gap-4 items-center">
                                            <label class="text-sm font-medium text-left col-span-1" for="ip">IP Address</label>
                                            <input id="ip" name="ip" type="text"
                                                class="ip form-input col-span-2 w-full px-3 py-1 bg-slate-100 rounded-md border border-slate-300"
                                                value="${address}" required disabled readonly />
                                        </div>

                                        <!-- MAC Address -->
                                        <div class="grid grid-cols-3 gap-4 items-center">
                                            <label class="text-sm font-medium text-left col-span-1" for="mac_address">Mac Address</label>
                                            <input id="mac_address" name="mac_address" type="text"
                                                class="mac_address form-input col-span-2 w-full px-3 py-1 bg-slate-100 rounded-md border border-slate-300"
                                                value="${mac_address}" required disabled readonly />
                                        </div>

                                        <!-- Host Name -->
                                        <div class="grid grid-cols-3 gap-4 items-center">
                                            <label class="text-sm font-medium text-left col-span-1" for="host_name">Host Name</label>
                                            <input id="host_name" name="host_name" type="text"
                                                class="host_name form-input col-span-2 w-full px-3 py-1 bg-slate-100 rounded-md border border-slate-300"
                                                value="${host_name}" required disabled readonly />
                                        </div>

                                        <!-- Firewall Dropdown -->
                                        <div class="grid grid-cols-3 gap-4 items-center">
                                            <label class="text-sm font-medium text-left col-span-1" for="firewall">Firewall</label>
                                            <select name="firewall" id="firewall"
                                                class="firewall form-input col-span-2 w-full px-3 py-1 rounded-md border border-slate-300 bg-white"
                                                required>
                                                @foreach ($firewalls as $firewall)                                                    
                                                    <option value="{{$firewall->firewall_name}}" ${status == '{{$firewall->firewall_name}}' ? 'selected':''}>
                                                        {{$firewall->firewall_name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal footer -->
                                <div class="px-5 py-4 border-t border-slate-200">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <button type="button"
                                            class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                            @click="modalOpen = false">Cancel</button>
                                        <button type="submit"
                                            class="btn-sm btn-update bg-indigo-500 hover:bg-indigo-600 text-white">Update</button>
                                    </div>
                                </div>
                            </form>
                        `);
                    },
                });
            });

            $('#deviceTable').on("click", ".btn-make",  function () {
                const id = $(this).data("id");
                const routerid = $(this).data("routerid");
                $("input[name!='_token']").val("");
                Swal.fire({
                    title: 'Please confirm!',
                    text: "Make this IP static?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: `/mikrotik/devices/make-static/${id}/${routerid}`,
                            // type: "GET",
                            // url: `/mikrotik/interface/${routerid}`,
                            success: function (response) {
                                console.info("response: ", response)
                                const { status, message } = response;
                                if (status == 1) {
                                    Swal.fire({
                                        icon : 'success',
                                        title: 'Success!',
                                        text: `IP has been set to static.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                    window.location.reload(true);
                                } else if (status == 2) {
                                    Swal.fire({
                                        icon : 'error',
                                        title: 'Cannot set to static!',
                                        text: `Please refresh the page and contact an admin.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function (data) {
                                console.info("error: ", data)
                            }
                        })

                    }
                })
            });

            $('#deviceTable').on("click", ".btn-delete",  function () {
                const id = $(this).data("id");
                const routerid = $(this).data("routerid");
                $("input[name!='_token']").val("");
                Swal.fire({
                    title: 'Please confirm!',
                    text: "Remove static IP?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: `/mikrotik/devices/delete-static/${id}/${routerid}`,
                            // type: "GET",
                            // url: `/mikrotik/interface/${routerid}`,
                            success: function (response) {
                                console.info("response: ", response)
                                const { status, message } = response;
                                if (status == 1) {
                                    Swal.fire({
                                        icon : 'success',
                                        title: 'Success!',
                                        text: `IP has been removed.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                    window.location.reload(true);
                                } else if (status == 2) {
                                    Swal.fire({
                                        icon : 'error',
                                        title: 'Cannot set to dynamic!',
                                        text: `Please refresh the page and contact an admin.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function (data) {
                                console.info("error: ", data)
                            }
                        })

                    }
                })
            });
        });
    </script>

    @endsection
</x-app-layout>

