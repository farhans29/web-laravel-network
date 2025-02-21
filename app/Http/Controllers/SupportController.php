<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikApiService;
use Illuminate\Support\Facades\Storage;

use App\Models\Router;
use App\Models\DhcpClient;
use App\Models\FirewallList;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportController extends Controller
{
    // PAGES
    public function allTicketsList()
    {

        return view('pages/support/all-tickets');
    }

    public function creatTicket()
    {
        // Get routers based on user's group
        $routers = DB::table('users')
            ->join('m_router', 'users.idusergrouping', '=', 'm_router.idusergrouping')
            ->where('users.id', auth()->id())
            ->select('m_router.idrouter', 'm_router.name as router_name')
            ->get();

        return view('pages/support/create-tickets', compact('routers'));
    }

    public function myTicketsList()
    {
        return view('pages/support/my-tickets');
    }

    public function assignedTicketsList()
    {
        return view('pages/support/assigned-tickets');
    }

    // API
    public function getAllTicketsData()
    {
        $tickets = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved',
                'created_at',
                'created_by',
            ])
            ->get();

        return response()->json([
            'data' => $tickets,
            'draw' => 1,
            'recordsTotal' => $tickets->count(),
            'recordsFiltered' => $tickets->count()
        ]);
    }
    public function getTicketById($id) {
        $ticketById = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved'
            ])
            ->where('idrec', $id)->first();
            
        return response()->json([
            'data' => $ticketById,
            'draw' => 1,
            'recordsTotal' => $ticketById->count(),
            'recordsFiltered' => $ticketById->count()
        ]);
    }

    public function getMyTicketsData() {
         $myTickets = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved',
                'created_at',
                'created_by',
            ])
            ->where('created_by', auth()->user()->id)
            ->get();

        return response()->json([
            'data' => $myTickets,
            'draw' => 1,
            'recordsTotal' => $myTickets->count(),
            'recordsFiltered' => $myTickets->count()
        ]);
    }

    public function getAssignedTicketsData() {
        $assignedTickets = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved'
            ])
            ->where(function($query) {
                $query->where('assigned_to', auth()->user()->id)
                      ->orWhereNull('assigned_to')
                      ->orWhere('assigned_to', 0);
            })
            ->get();

        return response()->json([
            'data' => $assignedTickets,
            'draw' => 1,
            'recordsTotal' => $assignedTickets->count(),
            'recordsFiltered' => $assignedTickets->count()
        ]);
    }

    private function isAdmin($roleId) {
        return in_array($roleId, [ 100, 101, 999, 888]);
    }

    public function viewTicket($ticketId)
    {
        $decodedTicketId = urldecode($ticketId);
        
        $ticket = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved',
                'created_by',
                'created_at',
                'updated_at'
            ])
            ->where('id_ticket', $decodedTicketId)
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
                'redirect' => route('support.tickets.list')
            ], 404);
        }

        // Get ticket replies
        $replies = DB::table('t_support_ticket_replies')
            ->select([
                't_support_ticket_replies.*',
                'users.username as admin_name'
            ])
            ->leftJoin('users', 't_support_ticket_replies.admin_id', '=', 'users.id')
            ->where('ticket_id', $decodedTicketId)
            ->orderBy('created_at', 'asc')
            ->get();

        $isAdmin = $this->isAdmin(auth()->user()->role);

        return view('pages/support/views/ticket-detail', compact('ticket', 'replies', 'isAdmin'));
    }
    public function viewTicketAdmin($ticketId)
    {
        // Check if user is admin
        if (!$this->isAdmin(auth()->user()->role)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access',
                'redirect' => route('support.tickets.my')
            ], 403);
        }

        $decodedTicketId = urldecode($ticketId);
        
        $ticket = DB::table('t_support_tickets')
            ->select([
                'id_ticket',
                'category_id',
                'due_date',
                'ticket_title',
                'ticket_body',
                'router_name',
                'name',
                'email',
                'ticket_priority',
                'ticket_status',
                'id_attachment_ticket',
                'resolution_notes',
                'last_reply_at',
                'is_resolved',
                'created_by',
                'created_at',
                'updated_at'
            ])
            ->where('id_ticket', $decodedTicketId)
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found',
                'redirect' => route('support.tickets.list')
            ], 404);
        }

        // Get ticket replies
        $replies = DB::table('t_support_ticket_replies')
            ->select([
                't_support_ticket_replies.*',
                'users.username as admin_name'
            ])
            ->leftJoin('users', 't_support_ticket_replies.admin_id', '=', 'users.id')
            ->where('ticket_id', $decodedTicketId)
            ->orderBy('created_at', 'asc')
            ->get();

        $isAdmin = true; // Always true for admin view

        return view('pages/support/views/ticket-detail-admin', compact('ticket', 'replies', 'isAdmin'));
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'user_id' => 'required',
                'name' => 'required|string',
                'email' => 'required|email',
                'ticket_title' => 'required|string|max:255',
                'router_name' => 'required|string',
                'ticket_priority' => 'required|in:Low,Medium,High,Urgent',
                'ticket_body' => 'required|string'
            ]);

            // Generate ticket ID
            $date = Carbon::now();
            
            // Extract first 3 letters from router name and convert to uppercase
            $routerCode = Str::upper(Str::substr($request->router_name, 0, 3));
            
            // Get count of tickets for today with this router code
            $lastTicket = DB::table('t_support_tickets')
                ->where('id_ticket', 'LIKE', "SUP/{$routerCode}/" . $date->format('ymd') . "/%")
                ->whereDate('created_at', $date)
                ->count();
            
            $ticketNumber = str_pad($lastTicket + 1, 3, '0', STR_PAD_LEFT);
            $ticketId = "SUP/{$routerCode}/" . $date->format('ymd') . '/' . $ticketNumber;

            // Create the ticket
            $ticket = DB::table('t_support_tickets')->insert([
                'id_ticket' => $ticketId,
                'ticket_title' => $request->ticket_title,
                'ticket_body' => $request->ticket_body,
                'router_name' => $request->router_name,
                'name' => $request->name,
                'email' => $request->email,
                'ticket_priority' => $request->ticket_priority,
                'ticket_status' => 'Open',
                'created_by' => $request->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket created successfully',
                'ticket_id' => $ticketId
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function closeTicket($ticketId)
    {
        try {
            // Decode the ticket ID
            $decodedTicketId = urldecode($ticketId);

            // Get the ticket
            $ticket = DB::table('t_support_tickets')
                ->where('id_ticket', $decodedTicketId)
                ->first();

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            // Update the ticket status
            DB::table('t_support_tickets')
                ->where('id_ticket', $decodedTicketId)
                ->update([
                    'ticket_status' => 'Closed',
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'updated_at' => now(),
                    'last_reply_at' => now(),
                    'resolution_notes' => 'Ticket closed by ' . auth()->user()->username
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket closed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to close ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitReply(Request $request)
    {
        try {
            $validated = $request->validate([
                'ticket_id' => 'required',
                'reply_message' => 'nullable|string',
                'admin_notes' => 'nullable|string',
                'attachment' => 'nullable|file|max:10240'
            ]);

            DB::beginTransaction();

            $attachmentId = null;

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                // Generate unique attachment ID using ticket_id
                $date = Carbon::now()->format('ymd');
                $attachmentId = "ATT/{$date}/" . $request->ticket_id;

                // Get original file name
                $originalName = $file->getClientOriginalName();
                
                // Store file with attachment ID as name
                $fileName = $attachmentId . '.' . $file->getClientOriginalExtension();
                $file->storeAs('ticket-attachments', $fileName, 'public');

                // Store attachment record
                DB::table('t_attachment')->insert([
                    'attachment_id' => $attachmentId,
                    'attachment_name' => $originalName,
                    'attachment_file' => $fileName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert reply with attachment ID
            DB::table('t_support_ticket_replies')->insert([
                'ticket_id' => $request->ticket_id,
                'admin_id' => auth()->id(),
                'reply_message' => $request->reply_message,
                'attachment_id' => $attachmentId,
                'admin_notes' => $request->admin_notes,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update the ticket's last reply timestamp
            DB::table('t_support_tickets')
                ->where('id_ticket', $request->ticket_id)
                ->update([
                    'last_reply_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reply submitted successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reply submission failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit reply: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add method to download attachments
    public function downloadAttachment($attachmentId)
    {
        $attachment = DB::table('t_attachment')
            ->where('attachment_id', $attachmentId)
            ->first();

        if (!$attachment) {
            abort(404, 'Attachment not found');
        }

        $filePath = storage_path('app/public/ticket-attachments/' . $attachment->attachment_file);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $attachment->attachment_name);
    }
}