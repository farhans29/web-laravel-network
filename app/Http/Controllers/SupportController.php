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
        $ticket = DB::table('t_support_tickets')->where('idrec', $id)->first();

        return response()->json($ticket);
    }

    public function getMyTicketsData() {
        $myTickets = DB::table('t_support_tickets')->where('created_by', auth()->user()->id)->get();

        return response()->json($myTickets);
    }

    public function getAssignedTicketsData() {
        $assignedTickets = DB::table('t_support_tickets')->where('assigned_to', auth()->user()->id)->get();

        return response()->json($assignedTickets);
    }
    
}