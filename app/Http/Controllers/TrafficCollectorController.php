<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;



class TrafficCollectorController extends Controller {
    public function index() {
    
    }

    // public function collectTrafficData(Request $request)
    // {
    //     // Get parameters from the MikroTik request
    //     // $serialNumber = $request->query('sn', 'unknown');
    //     $idRouter = $request->query('idr','unknown');
    //     $intTypes = $request->query('intp','PORT_X');
    //     $txBytes = (int) $request->query('tx', 0);
    //     $rxBytes = (int) $request->query('rx', 0);
    //     $datetime = $request->query('dt','2025-01-01');

    //     // Log data (optional)
    //     Log::info("Traffic Data - ID_R: $idRouter, TX: $txBytes, RX: $rxBytes");

    //     // Store in database (example)
    //     DB::table('t_traffic_logs')->insert([
    //         // 'serial_number' => $serialNumber,
    //         'id_router' => $idRouter,
    //         'tx_bytes' => $txBytes,
    //         'rx_bytes' => $rxBytes,
    //         'datetime' => $datetime,
    //         'timestamp' => now(),
    //     ]);

    //     return response()->json(['message' => 'Data received'], 200);
    // }
    public function collectTrafficData(Request $request)
    {
     

        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'idr'   => 'required|integer', // Ensure it's a valid router ID
                'intp'  => 'required|string|max:255', // Port type, string with a max length
                'tx'    => 'required|integer|min:0', // TX bytes must be a positive integer
                'rx'    => 'required|integer|min:0', // RX bytes must be a positive integer
                'dt'    => 'required|date_format:Y-m-d H:i:s', // Ensure proper datetime format
            ]);

            // Extract validated data
            $idRouter  = $validatedData['idr'];
            $intTypes  = $validatedData['intp'];
            $txBytes   = $validatedData['tx'];
            $rxBytes   = $validatedData['rx'];
            $datetime  = $validatedData['dt'];

            // Log data (optional)
            Log::info("Traffic Data - ID_R: $idRouter, INT: $intTypes, TX: $txBytes, RX: $rxBytes, DT: $datetime");

            // Store in database
            DB::table('t_traffic_logs')->insert([
                'idrouter'  => $idRouter,
                'int_type'  => $intTypes,
                'tx_bytes'   => $txBytes,
                'rx_bytes'   => $rxBytes,
                'datetime'   => $datetime,
                'timestamp'  => now(),
            ]);

            return response()->json(['message' => 'Data received successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error("Traffic Data Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to save data'], 500);
        }
    }

}