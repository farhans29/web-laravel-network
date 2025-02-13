
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Interfaces for {{ $router->name }}
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
            <table id="interfaceTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        {{-- <th class="text-center">*</th> --}}
                        <th class="text-center">Port</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">RX</th>
                        <th class="text-center">TX</th>
                        <th class="text-center">Running</th>
                        <th class="text-center">Enabled</th>
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
    
        $.ajax({
            // url: "http://network.integrated-os.cloud/mikrotik/interfaces/getDataJson/?idr=1",
            // type: "GET",
            // data: { idr: routerId }, // Dynamically set router ID
            url: "{{ route('mikrotik.interfaces-data-json') }}", // Generate the route dynamically
            type: "GET",
            data: { idr: routerId }, 
            success: function (response) {
                let interfaces = response[0] || [];
                let tableBody = "";
                let statusContainer = document.getElementById("statusContainer");
    
                let runningCount = 0;  // ✅ Initialize runningCount
                let enabledCount = 0;  // ✅ Initialize enabledCount
    
                interfaces.forEach(function (item) {
                    let rowColor = "background-color: #e0e0e0;";
                    let statusColor = "#6c757d";
                    let statusText = "Unknown";
    
                    if (item.running === "true" && item.disabled === "false") {
                        rowColor = 'background-color: #d4edda;';
                        statusColor = "#28a745";
                        statusText = "Running ✅";
                        runningCount++;  // ✅ Count running interfaces
                        enabledCount++;
                    } else if (item.running === "false" && item.disabled === "false") {
                        rowColor = 'background-color: #f5848e;';
                        statusColor = "#dc3545";
                        statusText = "Stopped ❌";
                        enabledCount++;
                    } else if (item.disabled === "true") {
                        statusColor = "#6c757d";
                        statusText = "Disabled ⚠️";
                    }
    
                    let statusBox = document.createElement("div");
                    statusBox.className = "p-2 text-white font-semibold text-xs text-center rounded-md shadow-sm";
                    statusBox.style.backgroundColor = statusColor;
                    statusBox.innerHTML = `<span>${item.name}</span><br><small>${statusText}</small>`;
                    statusContainer.appendChild(statusBox);
    
                    tableBody += `
                        <tr style="${rowColor}">
                            <td>${item.name}</td>
                            <td>${item["mac-address"]}</td>
                            <td>${item["rx-byte"]}</td>
                            <td>${item["tx-byte"]}</td>
                            <td class="text-center">${item.running === "true" ? "Running" : "Stopped"}</td>
                            <td class="text-center">${item.disabled === "false" ? "Enabled" : "Disabled"}</td>
                            <td>
                                <button class="btn-action" data-name="${item.name}">
                                    View MRTG
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
    
                let statusBlock = document.getElementById("statusBlock");
                if (runningCount === interfaces.length && enabledCount === interfaces.length) {
                    statusBlock.style.backgroundColor = "#28a745";
                    statusBlock.textContent = "All Interfaces Operational ✅";
                } else if (enabledCount > 0 && runningCount === 0) {
                    statusBlock.style.backgroundColor = "#dc3545";
                    statusBlock.textContent = "All Interfaces Stopped ❌";
                } else {
                    statusBlock.style.backgroundColor = "#6c757d";
                    statusBlock.textContent = "Partial Connection ⚠️";
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        });
    
        // ✅ Use event delegation to ensure buttons work after dynamic rendering
        $(document).on("click", ".btn-action", function () {
            let interfaceName = $(this).data("name");
            alert("You clicked on " + interfaceName);
        });
    });
    
    </script>
    

    {{-- <script>
        

        // // Action button event listener
        // $(".btn-action").on("click", function () {
        //     let interfaceName = $(this).data("name");
        //     let imageUrl = `/images/${interfaceName}.jpg`; // Adjust the path to where images are stored

        //     $("#modalImage").attr("src", imageUrl);
        //     $("#imageModal").removeClass("hidden");

        //     console.log("Modal opened for:", interfaceName);
        // });
        
        // $("#closeModal").on("click", function () {
        //     $("#imageModal").addClass("hidden");
        // });

        // // Close modal if clicked outside
        // $("#imageModal").on("click", function (event) {
        //     if ($(event.target).is("#imageModal")) {
        //         $("#imageModal").addClass("hidden");
        //     }
        // });

         $(document).ready(function () {
            $('#interface').DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                stateServe: true,
                // "order": [[ 1, "desc" ]],
                language: {
                    search: "Search Interface # : "
                },
                ajax: {
                    url: "{{ route('mikrotik.interfaces-data') }}",
                    data:function(d){
                        d.routerId = "{{ $router->idrouter }}"
                    }
                },
                columns: [
                    {
                        data: "name",
                        name: "name"
                    },
                    // {
                    //     data: "idreqform",
                    //     name: "idreqform"
                    // },
                    // {
                    //     data: "employee",
                    //     name: "employee"
                    // },
                    // {
                    //     data: "companyName",
                    //     name: "companyName"
                    // },
                    // {
                    //     data: "department",
                    //     name: "department"
                    // },
                    // {
                    //     data: "gtotal",
                    //     name: "gtotal"
                    // },
                    // {
                    //     data: "note",
                    //     name: "note"
                    // },
                    // {
                    //     data: "approvalstat",
                    //     name: "approvalstat"
                    // },
                    // {
                    //     data: "updated_at",
                    //     name: "updated_at"
                    // },
                    // {
                    //     data: "approval1Name",
                    //     name: "approval1Name"
                    // },
                    // {
                    //     data: "approval2Name",
                    //     name: "approval2Name"
                    // },
                    // {
                    //     data: "action2",
                    //     name: "action2"
                    // },
                ],
                // columnDefs: [
                //     { className: 'text-center', targets: [0, 1, 2, 6, 7, 8, 9,10] },
                //     { className: 'text-right', targets: [5] },
                // ], lengthMenu: [[30, 50, 100, -1], [30, 50, 100, 'All']],
                // Add row coloring logic based on the status for columns 8, 9, and 10
                // createdRow: function(row, data, dataIndex) {
                //         var status = data.approvalstat;
                        
                //         // Define the background color based on status
                //         var backgroundColorClass;
                //         if (status === "Draft") {
                //             backgroundColorClass = 'bg-gray-200';
                //         } else if (["Form Printed"].includes(status)) {
                //             backgroundColorClass = 'bg-sky-200';
                //         } else if (["Site Approved", "Waiting Approval 1", "HQ 1 Approved"].includes(status)) {
                //             backgroundColorClass = 'bg-yellow-200';
                //         } else if (["Payment Proof"].includes(status)) {
                //             backgroundColorClass = 'bg-green-200';
                //         } else if (["HQ 1 Denied", "HQ 2 Denied", "HQ 3 Denied", "Canceled"].includes(status)) {
                //             backgroundColorClass = 'bg-red-200';
                //         } else {
                //             backgroundColorClass = 'bg-white';
                //         }

                //         // Apply the background color and styling to columns 8, 9, and 10
                //         [7].forEach(function(columnIndex) {
                //             var $cell = $('td', row).eq(columnIndex);
                            
                //             // Wrap existing cell content in a styled div
                //             var $div = $('<div>', {
                //                 class: `text-sm text-center ${backgroundColorClass} rounded-md px-1 py-1 pb-3 pt-3`,
                //                 html: $cell.html() // Preserve existing content
                //             });
                            
                //             // Clear the cell and append the new styled div
                //             $cell.empty().append($div);
                //         });
                //     }
            });

            // $(".status").on('change', function (e) {
            //     e.preventDefault();
            //     $('#approval').DataTable().ajax.reload();
            // })
            // $(".company").on('change', function (e) {
            //     e.preventDefault();
            //     $('#approval').DataTable().ajax.reload();
            // })
            // $(".department").on('change', function (e) {
            //     e.preventDefault();
            //     $('#approval').DataTable().ajax.reload();
            // })

            // $('#approval').on("click", ".btn-cancel",  function () {
            //     const id = $(this).data("id");
            //     $("input[name!='_token']").val("");
            //     Swal.fire({
            //         title: 'Are you sure?',
            //         text: "Want to Cancel Reimburse Request!",
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonColor: '#3085d6',
            //         cancelButtonColor: '#d33',
            //         confirmButtonText: 'Yes, Cancel Request!'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             $.ajax({
            //                 headers: {
            //                     'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            //                 },
            //                 type: "POST",
            //                 url: `/ga/reimburse-approval/cancel/${id}`,
            //                 success: function (response) {
            //                     console.info("response: ", response)
            //                     const { status, message } = response;
            //                     if (status == 1) {
            //                         Swal.fire({
            //                             icon : 'success',
            //                             title: 'Success!',
            //                             text: `Reimburse Request has been Canceled.`,
            //                             confirmButtonColor: '#3085d6',
            //                             confirmButtonText: 'OK'
            //                         });
            //                         window.location.reload(true);
            //                     }
            //                 },
            //                 error: function (data) {
            //                     console.info("error: ", data)
            //                 }
            //             })

            //         }
            //     })
            // });
        });
    </script> --}}
    @endsection
</x-app-layout>

