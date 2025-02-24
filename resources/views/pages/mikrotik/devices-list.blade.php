
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
                "order": [[ 1, "desc" ]],
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
                    { className: 'text-center align-middle fixed-column text-base', targets: [0, 1, 3, 4, 5, 6] }, // Centered text
                    { className: 'text-left align-middle fixed-column text-base', targets: [2] }, // Left-aligned text
                ],
                lengthMenu: [[30, 50, 100, -1], [30, 50, 100, 'All']],
                autoWidth: false // Disable automatic resizing
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

