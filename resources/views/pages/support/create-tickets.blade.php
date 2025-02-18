<x-app-layout background="bg-white">
    <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="mb-4">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Create Ticket 📝</h1>
        </div>

        <div class="px-4 py-3">
            <div class="space-y-2">
                <form method="post" enctype="multipart/form-data" action="#"
                    id="form_create1">
                    @csrf

                    <div class="grid grid-cols-1 gap-3">
                        {{-- NAME AND EMAIL ROW --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- NAME --}}
                            <div class="mb-3">
                                <div class="mb-1">
                                    <label class="block text-sm font-medium" for="user_name">Name<span
                                            class="text-rose-500">*</span></label>
                                </div>
                                <div>
                                    <input id="user_name_input" name="user_name_input"
                                        class="form-input w-full px-2 py-1 read-only:bg-slate-200" type="text"
                                        value="{{ Auth::user()->username }}" readonly />
                                    <input id="user_id_input" name="user_id_input"
                                        class="form-input w-full px-2 py-1 read-only:bg-slate-200 hidden" type="text"
                                        value="{{ Auth::user()->id }}" readonly />
                                </div>
                            </div>

                            {{-- EMAIL --}}
                            <div class="mb-3">
                                <div class="mb-1">
                                    <label class="block text-sm font-medium" for="user_email">Email<span
                                            class="text-rose-500">*</span></label>
                                </div>
                                <div>
                                    <input id="user_email_input" name="user_email_input"
                                        class="form-input w-full px-2 py-1 read-only:bg-slate-200" type="text"
                                        value="{{ Auth::user()->email }}" readonly />
                                </div>
                            </div>
                        </div>
                        
                        {{-- TITLE --}}
                        <div class="mb-3">
                            <div class="mb-1">
                                <label class="block text-sm font-medium" for="ticket_title">Title<span
                                        class="text-rose-500">*</span></label>
                            </div>
                            <div>
                                <input id="ticket_title_input" name="ticket_title_input" class="form-input w-full px-2 py-1" type="text" placeholder="Enter Title" required />
                            </div>
                        </div>

                        {{-- DROPDOWN ROW --}}
                        <div class="grid grid-cols-2 gap-3">
                            {{-- DROPDOWN ROUTER NAME --}}
                            <div class="mb-3">
                                <div class="mb-1">
                                    <label class="block text-sm font-medium" for="router_name">Router Name<span
                                            class="text-rose-500">*</span></label>
                                </div>
                                <div>
                                    <select id="router_name_input" name="router_name_input" class="form-select w-full px-2 py-1" required>
                                        <option value="" selected>Select Router Name</option>
                                        <option value="XVNB-524">XVNB-524</option>
                                        <option value="GNAR-125">GNAR-125</option>
                                        <option value="VABX-430">VABX-430</option>
                                    </select>
                                </div>
                            </div>

                            {{-- DROPDOWN PRIORITY --}}
                            <div class="mb-3">
                                <div class="mb-1">
                                    <label class="block text-sm font-medium" for="ticket_priority">Priority<span
                                            class="text-rose-500">*</span></label>
                                </div>
                                <div>
                                    <select id="ticket_priority_input" name="ticket_priority_input" class="form-select w-full px-2 py-1" required>
                                        <option value="">Select Priority</option>
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- TICKET BODY --}}
                        <div class="mb-3">
                            <div class="mb-1">
                                <label class="block text-sm font-medium" for="ticket_body">Message<span
                                        class="text-rose-500">*</span></label>
                            </div>
                            <div>
                                <textarea 
                                    id="ticket_body_input" 
                                    name="ticket_body_input" 
                                    class="form-textarea w-full px-2 py-1 min-h-[120px]" 
                                    rows="3"
                                    placeholder="Enter Message"
                                    required
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <center><button class="btn bg-emerald-500 hover:bg-emerald-600 text-white mt-4" type="submit"
                            id="create_offer">
                            <span class="xs:block mx-4">Submit Ticket</span>
                        </button> 
                    </center>
                </form>
            </div>
        </div>

    </div>
    @section('js-page')
        <script>
            
        </script>
    @endsection
</x-app-layout>
