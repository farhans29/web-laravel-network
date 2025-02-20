@props(['replies', 'ticket', 'isAdmin'])

<div class="bg-white rounded-lg shadow-lg p-6 mt-6">
    <h2 class="text-xl font-bold mb-4">Ticket Replies</h2>
    
    <!-- Replies List -->
    <div class="space-y-4 mb-6" id="repliesContainer">
        @forelse($replies as $reply)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="font-semibold">
                            @if($reply->admin_id)
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs">Admin</span>
                                {{ $reply->admin_name }}
                            @else
                                {{ $reply->user_name ?? 'Customer' }}
                            @endif
                        </span>
                        <span class="text-gray-500 text-sm ml-2">
                            {{ \Carbon\Carbon::parse($reply->created_at)->format('d M Y, H:i') }}
                        </span>
                    </div>
                </div>
                <p class="text-gray-700 whitespace-pre-line">{{ $reply->reply_message }}</p>
                @if($reply->attachment_id)
                    <div class="mt-2">
                        <a href="{{ route('support.tickets.download.reply.attachment', $reply->attachment_id) }}" 
                           class="text-blue-500 hover:text-blue-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                </path>
                            </svg>
                            View Attachment
                        </a>
                    </div>
                @endif
                @if($reply->admin_notes && $isAdmin)
                    <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold text-yellow-600">Admin Notes:</span> 
                            {{ $reply->admin_notes }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-gray-500 py-4">
                No replies yet
            </div>
        @endforelse
    </div>

    @if($ticket->ticket_status !== 'Closed')
        <!-- Reply Form -->
        <form id="replyForm" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id_ticket }}">
            
            <div>
                <label class="block text-sm font-medium mb-2" for="reply_message">
                    Your Reply<span class="text-rose-500">*</span>
                </label>
                <textarea id="reply_message" name="reply_message" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required></textarea>
            </div>

            @if($isAdmin)
            <div>
                <label class="block text-sm font-medium mb-2" for="admin_notes">
                    Admin Notes (Only visible to admins)
                </label>
                <textarea id="admin_notes" name="admin_notes" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-2" for="attachment">
                    Attachment (optional)
                </label>
                <input type="file" id="attachment" name="attachment"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex justify-end">
                <button type="button" id="submitReply"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Submit Reply
                </button>
            </div>
        </form>
    @else
        <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-500">
            This ticket is closed. No new replies can be added.
        </div>
    @endif
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Debug: Log when button is found
    const submitButton = document.getElementById('submitReply');

    // Add click event listener using vanilla JS
    document.getElementById('submitReply').addEventListener('click', function() {
        // console.log('Submit button clicked'); // Debug log
        
        const form = document.getElementById('replyForm');
        const formData = new FormData(form);

        // Debug log form data
        // console.log('Form Data:', {
        //     ticket_id: formData.get('ticket_id'),
        //     reply_message: formData.get('reply_message'),
        //     admin_notes: formData.get('admin_notes'),
        //     attachment: formData.get('attachment')
        // });

        // Validate required fields
        const replyMessage = formData.get('reply_message').trim();
        if (!replyMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Reply message is required',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Submitting reply...',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit form via fetch API
        fetch('{{ route('support.tickets.reply') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            // console.log('Success Response:', data);
            
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Reply submitted successfully',
                    showConfirmButton: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Something went wrong!',
                confirmButtonText: 'OK'
            });
        });
    });

    // File input validation
    document.getElementById('attachment').addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.size > 10 * 1024 * 1024) { // 10MB limit
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Please select a file smaller than 10MB',
                confirmButtonText: 'OK'
            });
            this.value = '';
        }
    });

    // Add visual feedback for required fields
    document.getElementById('reply_message').addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        } else {
            this.classList.remove('border-gray-300');
            this.classList.add('border-red-500');
        }
    });
});
</script>
@endpush 