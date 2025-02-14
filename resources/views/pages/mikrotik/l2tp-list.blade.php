
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    VPN for {{ $router->name }}
                    @if(!isset($router) || !$router)
                        <p class="text-red-500">Error: Router not found</p>
                    @endif
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>
        
        {{-- <div id="statusContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 mb-4">
        <!-- Status boxes will be dynamically added here -->
        </div> --}}

        <!-- Table -->
        <div class="table-responsive">
            <table id="interfaceTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        {{-- <th class="text-center">*</th> --}}
                        <th class="text-center">User</th>
                        <th class="text-center">VPN Type</th>
                        <th class="text-center">Caller ID</th>
                        <th class="text-center">Connected From</th>
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

            let requestUrl = "{{ route('mikrotik.l2tp-data-json') }}"; // Laravel route
            let fullUrl = `${requestUrl}?idr=${routerId}`; // Append router ID dynamically

            fetchInterfacesForTable(fullUrl);
            // fetchInterfacesForStatus(fullUrl);

            // // ✅ Event delegation for dynamic button clicks
            // $(document).on("click", ".btn-action", function () {
            //     let interfaceName = $(this).data("name").replace(/ /g, "%20");
            //     let routerIp = "{{ $router->ip }}"; // Ensure this value is passed correctly
            //     let routerPort = "{{ $router->web_port }}"; 

            //     let url = `http://${routerIp}:${routerPort}/graphs/iface/${interfaceName}`;
            //     window.open(url, "_blank");
            // });
        });

        /**
         * Fetch interfaces data for the DataTable
         */
        function fetchInterfacesForTable(url) {
            $.ajax({
                url: url,
                type: "GET",
                success: function (response) {
                    let interfaces = response[0] || [];
                    let tableBody = "";

                    interfaces.forEach(function (item) {
                        let rowColor = "background-color: #e0e0e0;";
                        if (item.running === "true" && item.disabled === "false") {
                            rowColor = 'background-color: #d4edda;'; // Green for Running
                        } else if (item.running === "false" && item.disabled === "false") {
                            rowColor = 'background-color: #f5848e;'; // Red for Stopped
                        }

                        tableBody += `
                            <tr style="${rowColor}">
                                <td class="text-center">${item.name}</td>
                                <td class="text-center">${item["service"]}</td>
                                <td class="text-center">${item["caller-id"]}</td>
                                <td class="text-center">${item["uptime"]}</td>
                                <td class="text-center flex justify-center items-center">
                                    <button class="btn btn-sm text-sm text-white flex items-center justify-center px-4 py-2 ml-1"
                                        style="background-color: rgb(239, 68, 68); transition: background-color 0.3s ease-in-out;" 
                                        data-name="${item.name}">
                                        ❌ <span class="ml-2">Disconnect User</span>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $("#interfaceTable tbody").html(tableBody);
                    $("#interfaceTable").DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        lengthMenu: [[20, 25, 50, 100], [20, 25, 50, 100]],
                        layout: {
                            topStart: 'info',
                            bottom: 'paging',
                            bottomStart: null,
                            bottomEnd: null
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching DataTable data:", error);
                }
            });
        }

        /**
         * Fetch interfaces data for statusContainer
         */
        // function fetchInterfacesForStatus(url) {
        //     $.ajax({
        //         url: url,
        //         type: "GET",
        //         success: function (response) {
        //             let interfaces = response[0] || [];
        //             let statusContainer = document.getElementById("statusContainer");
        //             let statusBlock = document.getElementById("statusBlock");

        //             statusContainer.innerHTML = ""; // Clear previous content

        //             let runningCount = 0;
        //             let enabledCount = 0;

        //             interfaces.forEach(function (item) {
        //                 let statusColor = "#6c757d";
        //                 let statusText = "Unknown";

        //                 if (item.running === "true" && item.disabled === "false") {
        //                     statusColor = "#28a745"; // Green
        //                     statusText = "Running ✅";
        //                     runningCount++;
        //                     enabledCount++;
        //                 } else if (item.running === "false" && item.disabled === "false") {
        //                     statusColor = "#dc3545"; // Red
        //                     statusText = "Stopped ❌";
        //                     enabledCount++;
        //                 } else if (item.disabled === "true") {
        //                     statusText = "Disabled ⚠️";
        //                 }

        //                 let statusBox = document.createElement("div");
        //                 statusBox.className = "p-2 text-white font-semibold text-xs text-center rounded-md shadow-sm";
        //                 statusBox.style.backgroundColor = statusColor;
        //                 statusBox.innerHTML = `<span>${item.name}</span><br><small>${statusText}</small>`;
        //                 statusContainer.appendChild(statusBox);
        //             });

        //             // Update statusBlock based on conditions
        //             if (runningCount === interfaces.length && enabledCount === interfaces.length) {
        //                 statusBlock.style.backgroundColor = "#28a745";
        //                 statusBlock.textContent = "All Interfaces Operational ✅";
        //             } else if (enabledCount > 0 && runningCount === 0) {
        //                 statusBlock.style.backgroundColor = "#dc3545";
        //                 statusBlock.textContent = "All Interfaces Stopped ❌";
        //             } else {
        //                 statusBlock.style.backgroundColor = "#6c757d";
        //                 statusBlock.textContent = "Partial Connection ⚠️";
        //             }
        //         },
        //         error: function (xhr, status, error) {
        //             console.error("Error fetching statusContainer data:", error);
        //         }
        //     });
        // }

    </script>
    @endsection
</x-app-layout>

