<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MikrotikApiService;

use App\Models\Router;
use App\Models\DhcpClient;
use App\Models\FirewallList;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    // PAGES
    public function allTicketsList()
    {

        return view('pages/support/all-tickets');
    }

    public function creatTicket()
    {
        return view('pages/support/create-tickets');
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
                'is_resolved'
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
                'is_resolved'
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

    public function viewTicket($ticketId)
    {
        // Decode the URL-encoded ticket ID
        $decodedTicketId = urldecode($ticketId);
        
        // Get the ticket details
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

        return view('pages/support/ticket-detail', compact('ticket'));
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
            $lastTicket = DB::table('t_support_tickets')
                ->whereDate('created_at', $date)
                ->count();
            $ticketNumber = str_pad($lastTicket + 1, 3, '0', STR_PAD_LEFT);
            $ticketId = 'SUP/RT0/' . $date->format('ymd') . '/' . $ticketNumber;

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
}