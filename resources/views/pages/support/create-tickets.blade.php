<x-app-layout background="bg-white">
    <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Create Ticket 📝</h1>
            <p class="text-sm text-slate-600 mt-1">Submit a new support ticket for assistance</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto">
            <div class="p-6">
                <form method="POST" enctype="multipart/form-data" action="{{ route('support.tickets.store') }}" id="form_create1">
                    @csrf

                    <div class="space-y-6">
                        {{-- NAME AND EMAIL ROW --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- NAME --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="user_name">
                                    Name<span class="text-rose-500">*</span>
                                </label>
                                <input id="user_name_input" name="user_name_input"
                                    class="form-input w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-primary-500"
                                    type="text" value="{{ Auth::user()->username }}" readonly />
                                <input id="user_id_input" name="user_id_input" type="hidden"
                                    value="{{ Auth::user()->id }}" />
                            </div>

                            {{-- EMAIL --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="user_email">
                                    Email<span class="text-rose-500">*</span>
                                </label>
                                <input id="user_email_input" name="user_email_input"
                                    class="form-input w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-primary-500"
                                    type="text" value="{{ Auth::user()->email }}" readonly />
                            </div>
                        </div>
                        
                        {{-- TITLE --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" for="ticket_title">
                                Title<span class="text-rose-500">*</span>
                            </label>
                            <input id="ticket_title_input" name="ticket_title_input" 
                                class="form-input w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-primary-500" 
                                type="text" placeholder="Enter a descriptive title" required />
                        </div>

                        {{-- DROPDOWN ROW --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- DROPDOWN ROUTER NAME --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="router_name">
                                    Router Name<span class="text-rose-500">*</span>
                                </label>
                                <select id="router_name_input" name="router_name_input" 
                                    class="form-select w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-primary-500" required>
                                    <option value="" selected disabled>Select Router Name</option>
                                    @foreach($routers as $router)
                                        <option value="{{ $router->idrouter }}" data-name="{{ $router->router_name }}">
                                            {{ $router->router_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="router_id_input" name="router_id_input">
                            </div>

                            {{-- DROPDOWN PRIORITY --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" for="ticket_priority">
                                    Priority<span class="text-rose-500">*</span>
                                </label>
                                <select id="ticket_priority_input" name="ticket_priority_input" 
                                    class="form-select w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-primary-500" required>
                                    <option value="" disabled>Select Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        {{-- TICKET BODY --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" for="ticket_body">
                                Message<span class="text-rose-500">*</span>
                            </label>
                            <textarea id="ticket_body_input" name="ticket_body_input" 
                                class="form-textarea w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-primary-500" 
                                rows="6" placeholder="Describe your issue in detail" required></textarea>
                        </div>

                        {{-- SUBMIT BUTTON --}}
                        <div class="flex justify-center pt-4">
                            <button type="submit" id="create_ticket"
                                class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-8 py-2 rounded-md transition-colors duration-150 ease-in-out">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Submit Ticket
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('js-page')
    <script>
        $(document).ready(function() {
            // Initialize select2 for router dropdown
            $('#router_name_input').select2({
                placeholder: 'Select Router Name',
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                // When selection changes, update the hidden input with the router ID
                const selectedOption = $(this).find('option:selected');
                $('#router_id_input').val($(this).val());
            });

            $('#form_create1').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    user_id: $('#user_id_input').val(),
                    name: $('#user_name_input').val(),
                    email: $('#user_email_input').val(),
                    ticket_title: $('#ticket_title_input').val(),
                    router_id: $('#router_id_input').val(),  // Send router ID
                    router_name: $('#router_name_input option:selected').text().trim(), // Send router name
                    ticket_priority: $('#ticket_priority_input').val(),
                    ticket_body: $('#ticket_body_input').val(),
                };

                // Show loading state
                Swal.fire({
                    title: 'Creating ticket...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit the form
                $.ajax({
                    url: '{{ route('support.tickets.store') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Ticket created successfully',
                            showConfirmButton: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('support.tickets.my') }}';
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong!',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
    @endsection
</x-app-layout>
