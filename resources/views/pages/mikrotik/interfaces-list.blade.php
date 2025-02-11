
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Interfaces for {{ $router->name }}
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>

        <!-- Table -->
        {{-- <h1>Interfaces for {{ $router->name }}</h1> --}}

        <div class="table-responsive">
            <table id="interface" class="table table-striped table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        {{-- <th class="text-center">Port</th> --}}
                        <th class="text-center">Name</th>
                        {{-- <th class="text-center">Status</th>
                        <th class="text-center">TX</th>
                        <th class="text-center">RX</th>
                        <th class="text-center">Action</th> --}}
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @section('js-page')
    <script>
         $(document).ready(function () {
            $('#interface').DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                stateServe: true,
                "order": [[ 1, "desc" ]],
                language: {
                    search: "Search Reimburse Request # : "
                },
                ajax: {
                    url: "{{ route('mikrotik.interfaces', ['routerId' => $router->idrouter]) }}",
                    data:function(d){
                        d.status = $("#status").val()
                        d.company = $("#company").val()
                        d.department = $("#department").val()
                    }
                },
                columns: [
                    {
                        data: "address",
                        name: "address"
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

            $(".status").on('change', function (e) {
                e.preventDefault();
                $('#approval').DataTable().ajax.reload();
            })
            $(".company").on('change', function (e) {
                e.preventDefault();
                $('#approval').DataTable().ajax.reload();
            })
            $(".department").on('change', function (e) {
                e.preventDefault();
                $('#approval').DataTable().ajax.reload();
            })

            $('#approval').on("click", ".btn-cancel",  function () {
                const id = $(this).data("id");
                $("input[name!='_token']").val("");
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Want to Cancel Reimburse Request!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Cancel Request!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: `/ga/reimburse-approval/cancel/${id}`,
                            success: function (response) {
                                console.info("response: ", response)
                                const { status, message } = response;
                                if (status == 1) {
                                    Swal.fire({
                                        icon : 'success',
                                        title: 'Success!',
                                        text: `Reimburse Request has been Canceled.`,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                    window.location.reload(true);
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

