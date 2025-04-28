
<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Firewall List for {{ $router->name }}
                    @if(!isset($router) || !$router)
                        <p class="text-red-500">Error: Router not found</p>
                    @endif
                </h1>
            </div>
        </div>

        <!-- label -->
        @if (Auth::user()->role == '101')
            <div class="flex flex-row text-xs">
                <label class="flex flex-row text-xs">
                    <div x-data="{ modalOpen: false }">
                        <button class="btn bg-purple-500 hover:bg-purple-600 text-white text-xs mb-3"
                            @click.prevent="modalOpen = true" aria-controls="feedback-modal">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>&nbsp; Create New Firewall List</button>
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
                                        <div class="font-semibold text-slate-800">Create New Firewall List</div>
                                        <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                            <div class="sr-only">Close</div>
                                            <svg class="w-4 h-4 fill-current">
                                                <path
                                                    d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <form action="{{ route('mikrotik.change-firewall', ['routerId' => $router->idrouter]) }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <!-- Modal content -->
                                    <div class="px-5 py-4">
                                        <div class="space-y-3">
                                            <div class="space-y-3">
                                                <!-- IP Address -->
                                                <div class="grid grid-cols-3 gap-4 items-center">
                                                    <label class="text-sm font-medium text-left col-span-1" for="ip">IP Address</label>
                                                    <input id="ip" name="ip" type="text"
                                                        class="ip form-input col-span-2 w-full px-3 py-1 bg-white rounded-md border border-slate-300"
                                                        required />
                                                </div>
        
                                                {{-- <!-- MAC Address -->
                                                <div class="grid grid-cols-3 gap-4 items-center">
                                                    <label class="text-sm font-medium text-left col-span-1" for="mac_address">Mac Address</label>
                                                    <input id="mac_address" name="mac_address" type="text"
                                                        class="mac_address form-input col-span-2 w-full px-3 py-1 bg-white rounded-md border border-slate-300"
                                                        required />
                                                </div> --}}
        
                                                {{-- <!-- Host Name -->
                                                <div class="grid grid-cols-3 gap-4 items-center">
                                                    <label class="text-sm font-medium text-left col-span-1" for="host_name">Host Name</label>
                                                    <input id="host_name" name="host_name" type="text"
                                                        class="host_name form-input col-span-2 w-full px-3 py-1 bg-white rounded-md border border-slate-300"
                                                        required />
                                                </div> --}}
        
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

                                                <div class="grid grid-cols-3 gap-4 items-center">
                                                    <label class="text-sm font-medium text-left col-span-1" for="user">User</label>
                                                    <input id="user" name="user" type="text"
                                                        class="user form-input col-span-2 w-full px-3 py-1 bg-white rounded-md border border-slate-300"
                                                        required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="px-5 py-4 border-t border-slate-200">
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button type="button"
                                                class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                                @click="modalOpen = false">Cancel</button>
                                            <button type="submit" id="submit"
                                                class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Create</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        @endif

        <!-- Table -->
        <div class="table-responsive">
            <table id="firewallTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        {{-- <th class="text-center">*</th> --}}
                        <th class="text-center">Address</th>
                        <th class="text-center">List</th>
                        @if (Auth::user()->id == 1)
                            <th class="text-center">Action</th>
                        @endif
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
            
            let columns = [
                { data: "address", name: "address" },
                { data: "list", name: "list" }
            ];

            @if(Auth::user()->id == 1)
                columns.push({ data: "action", name: "action" });
            @endif

            $('#firewallTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                stateServe: true,
                "order": [[0, "asc"]],
                language: {
                    search: "Search Firewall Table # : "
                },
                ajax: {
                    url: "{{ route('mikrotik.firewall-data') }}",
                    data: function (d) {
                        d.routerId = "{{$router->idrouter}}";
                    }
                },
                columns: columns,
                columnDefs: [
                    { className: 'text-center align-middle fixed-column text-base', targets: [0, 1] }, // Centered text
                    @if(Auth::user()->id == 1)
                        { className: 'text-center align-middle fixed-column text-base', targets: [2] }, // Apply to Action column
                    @endif
                ],
                lengthMenu: [[30, 50, 100, -1], [30, 50, 100, 'All']],
                autoWidth: false // Disable automatic resizing
            });
            
        });

        $('#firewallTable').on("click", ".btn-firewall", function () {
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
                            <form method="post" class="type_update" enctype="multipart/form-data" action="/mikrotik/firewall/change-firewall/${routerid}/${address}">
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

        $('#firewallTable').on("click", ".btn-delete",  function () {
            const id = $(this).data("id");
            const ip = $(this).data("ip");
            const routerid = $(this).data("routerid");

            $("input[name!='_token']").val("");
            Swal.fire({
                title: 'Are you sure',
                text: `Want to delete ${ip} - ${id}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        url: `/mikrotik/firewall/remove/${routerid}/${id}`,
                        success: function (response) {
                            console.info("response: ", response)
                            const { status, message } = response;
                            if (status == 1) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: `${ip} has been Deleted.`,
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
    </script>

    @endsection
</x-app-layout>

