<x-app-layout>
    @push('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    @endpush

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    All Tickets
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
                        <th class="text-center">Category</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Title</th>
                        <th class="text-center">Due Date</th>
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
                    { 
                        data: 'category_id',
                        render: function(data, type, row) {
                            return data;
                        },
                        className: 'text-center'
                    },
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
                    { 
                        data: 'due_date',
                        render: function(data, type, row) {
                            if (!data) return '';
                            const date = new Date(data);
                            return date.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="flex items-center justify-center space-x-2">
                                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded-md text-xs">
                                        View
                                    </button>
                                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-md text-xs">
                                        Edit
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']],
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
    </script>
    @endsection
</x-app-layout>

