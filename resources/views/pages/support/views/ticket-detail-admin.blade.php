<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">
                    Ticket Details #{{ $ticket->id_ticket }}
                </h1>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('support.tickets.list') }}" 
                   class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">
                    ← Back to Tickets
                </a>

                @if($ticket->ticket_status !== 'Closed')
                <button onclick="closeTicket('{{ $ticket->id_ticket }}')"
                    class="btn bg-red-500 hover:bg-red-600 text-white">
                    Close Ticket
                </button>
                @endif
            </div>
        </div>

        <!-- Ticket Content -->
        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <div class="p-5">
                <!-- Title and Priority -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-800">{{ $ticket->ticket_title }}</h2>
                    <span class="@if($ticket->ticket_priority === 'urgent' || $ticket->ticket_priority === 'Urgent') 
                                 bg-red-100 text-red-600 
                               @elseif($ticket->ticket_priority === 'high' || $ticket->ticket_priority === 'High')
                                 bg-yellow-100 text-orange-600
                               @elseif($ticket->ticket_priority === 'medium' || $ticket->ticket_priority === 'Medium')
                                 bg-blue-100 text-blue-600
                               @else
                                 bg-green-100 text-green-600
                               @endif 
                               px-3 py-1 rounded-full text-sm font-semibold">
                        {{ ucfirst($ticket->ticket_priority) }}
                    </span>
                </div>

                <!-- Customer Information -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Customer Information</h3>
                    <div class="bg-slate-50 p-4 rounded-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm"><span class="font-medium">Name:</span> {{ $ticket->name }}</p>
                                <p class="text-sm"><span class="font-medium">Email:</span> {{ $ticket->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm"><span class="font-medium">Router:</span> {{ $ticket->router_name }}</p>
                                <p class="text-sm"><span class="font-medium">Status:</span> 
                                    <span class="@if($ticket->ticket_status === 'open' || $ticket->ticket_status === 'Open')
                                                 bg-emerald-100 text-emerald-600
                                               @elseif($ticket->ticket_status === 'in progress' || $ticket->ticket_status === 'In Progress' || $ticket->ticket_status === 'in_progress')
                                                 bg-amber-100 text-amber-600
                                               @else
                                                 bg-slate-100 text-slate-500
                                               @endif
                                               px-2 py-0.5 rounded-full text-xs font-medium inline-block ml-1">
                                        {{ ucfirst($ticket->ticket_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Description -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Description</h3>
                    <div class="bg-slate-50 p-4 rounded-sm">
                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $ticket->ticket_body }}</p>
                    </div>
                </div>

                <!-- Resolution Notes -->
                @if($ticket->resolution_notes)
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Resolution Notes</h3>
                    <div class="bg-slate-50 p-4 rounded-sm">
                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $ticket->resolution_notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-4 text-sm text-slate-600">
                    <div>
                        <p><span class="font-medium">Created:</span> 
                           {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p><span class="font-medium">Last Updated:</span> 
                           {{ \Carbon\Carbon::parse($ticket->updated_at)->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add the replies component -->
        <x-ticket.replies :replies="$replies" :ticket="$ticket" :isAdmin="$isAdmin" />
    </div>

    @section('js-page')
    <script>
        function closeTicket(ticketId) {
            Swal.fire({
                title: 'Close Ticket?',
                text: 'Are you sure you want to close this ticket?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, close it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Closing ticket...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send close request
                    $.ajax({
                        url: '{{ route('support.tickets.close', ':id') }}'.replace(':id', ticketId),
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Ticket has been closed',
                                showConfirmButton: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to close ticket',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    </script>
    @endsection
</x-app-layout> 