<x-app-layout>

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    All Tickets 📝
                </h1>
            </div>
        </div>

        <!-- label -->
        <div class="flex flex-row text-xs mb-3">
        </div>
        
        <!-- Table -->
        <div class="table-responsive">
            <table id="ticketsTable" class="relative table table-bordered text-xs" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Ticket ID</th>
                        {{-- <th class="text-center">Category</th> --}}
                        <th class="text-center">Customer Name</th>
                        <th class="text-center">Title</th>
                        {{-- <th class="text-center">Due Date</th> --}}
                        <th class="text-center">Status</th>
                        <th class="text-center">Priority</th>
                        <th class="text-center">Created At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data wil be inserted here --}}
                </tbody>
            </table>
        </div>
    </div>

    @section('js-page')
    <script>
        // Add this at the top of your script
        const viewTicketBaseUrl = '{{ route('support.tickets.view-admin', ':id') }}';

        $(document).ready(function() {
            $('#ticketsTable').DataTable({
                ajax: {
                    url: '{{ route('support.tickets.allDatas') }}',
                    type: 'GET',
                    dataSrc: function(json) {
                        
                        return json.data;
                    }
                },
                columns: [
                    { 
                        data: 'id_ticket',
                        render: function(data, type, row) {
                            return data;
                        },
                        className: 'text-center'
                    },
                    // { 
                    //     data: 'category_id',
                    //     render: function(data, type, row) {
                    //         return data;
                    //     },
                    //     className: 'text-center'
                    // },
                    { 
                        data: 'name',
                        render: function(data, type, row) {
                            return data;
                        },
                        className: 'text-left'
                    },
                    { 
                        data: 'ticket_title',
                        render: function(data, type, row) {
                            return data;
                        },
                        className: 'text-left'
                    },
                    // { 
                    //     data: 'due_date',
                    //     render: function(data, type, row) {
                    //         if (!data) return '';
                    //         const date = new Date(data);
                    //         return date.toLocaleDateString('en-GB', {
                    //             day: '2-digit',
                    //             month: 'short',
                    //             year: 'numeric'
                    //         });
                    //     }
                    // },
                    
                    {
                        data: 'ticket_status',
                        render: function(data, type, row) {
                            data = data ? data.toLowerCase() : '';
                            if (data === 'open') {
                                return '<span class="bg-emerald-100 text-emerald-600 px-2.5 py-0.5 rounded-full font-medium">Open</span>';
                            } else if (data === 'in progress' || data === 'in_progress') {
                                return '<span class="bg-amber-100 text-amber-600 px-2.5 py-0.5 rounded-full font-medium">In Progress</span>';
                            } else if (data === 'closed' || data === 'resolved') {
                                return '<span class="bg-slate-100 text-slate-500 px-2.5 py-0.5 rounded-full font-medium">Closed</span>';
                            } else {
                                return '<span class="bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full font-medium">' + (data || 'Unknown') + '</span>';
                            }
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'ticket_priority',
                        render: function(data, type, row) {
                            data = data ? data.toLowerCase() : '';
                            if (data === 'urgent') {
                                return '<span class="bg-red-100 text-red-600 px-2.5 py-0.5 rounded-full font-medium">Urgent</span>';
                            } else if (data === 'high') {
                                return '<span class="bg-yellow-100 text-orange-600 px-2.5 py-0.5 rounded-full font-medium">High</span>';
                            } else if (data === 'medium') {
                                return '<span class="bg-blue-100 text-blue-600 px-2.5 py-0.5 rounded-full font-medium">Medium</span>';
                            } else if (data === 'low') {
                                return '<span class="bg-green-100 text-green-600 px-2.5 py-0.5 rounded-full font-medium">Low</span>';
                            } else {
                                return '<span class="bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full font-medium">' + (data || 'Unknown') + '</span>';
                            }
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        render: function(data, type, row) {
                            const date = new Date(data);
                            return date.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        },
                        className: 'text-center'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            // Double encode the ID to handle special characters properly
                            const encodedId = encodeURIComponent(row.id_ticket);
                            const viewUrl = viewTicketBaseUrl.replace(':id', encodedId);
                            return `
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="javascript:void(0)" 
                                       onclick="viewTicket('${viewUrl}')"
                                       class="bg-cyan-500 hover:bg-cyan-600 text-white px-2 py-1 rounded-md text-xs">
                                        View
                                    </a>
                                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-md text-xs">
                                        Assign To
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[5, 'desc']],
                responsive: true,
                pageLength: 10,
                processing: true,
                language: {
                    search: 'Search tickets:',
                    emptyTable: 'No tickets available',
                    loadingRecords: 'Loading tickets...',
                    zeroRecords: 'No matching tickets found'
                },
                drawCallback: function(settings) {
                    // if (settings.json) {
                    //     console.log('Draw complete. Total records:', settings.json.data.length);
                    // }
                }
            });
        });

        // Add this function to handle ticket viewing
        function viewTicket(url) {
            fetch(url)
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    }
                    return response.text();
                })
                .then(data => {
                    if (typeof data === 'object' && data.status === 'error') {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        // If it's HTML, redirect to the URL instead of replacing document
                        window.location.href = url;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    </script>
    @endsection
</x-app-layout>

