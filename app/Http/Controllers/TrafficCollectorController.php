<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TrafficCollectorController extends Controller {
    public function index() {

    }

    public function collectTrafficData(Request $request)
    {
        try {
            // Define your secret key (store this securely in .env file)
            $secretKey = env('SECRET_KEY', 'default_secret');

            // Check if the secret key is provided before validation
            if ($request->input('key') !== $secretKey) {
                return response()->json(['error' => 'Unauthorized request'], 403);
            }

            // Validate the incoming request
            $validatedData = $request->validate([
                'idr'   => 'required|string', // Ensure it's a valid router ID
                'intp'  => 'required|string|max:255', // Port type, string with a max length
                'tx'    => 'required|integer|min:0', // TX bytes must be a positive integer
                'rx'    => 'required|integer|min:0', // RX bytes must be a positive integer
                'dt'    => 'required|string', // Ensure proper datetime format
            ]);

            // Extract validated data
            $idRouter  = $validatedData['idr'];
            $intTypes  = $validatedData['intp'];
            $txBytes   = $validatedData['tx'];
            $rxBytes   = $validatedData['rx'];
            $dtInput  = $validatedData['dt']; // Convert to Carbon instance

            // Convert dt format (Example: feb/12/2025 10:25:06 → 2025-02-12 10:25:06)
            try {
                $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid datetime format. Use: mmm/DD/YYYY H:m:s'], 422);
            }
            
            // Log data (optional)
            // Log::info("Traffic Data - ID_R: $idRouter, INT: $intTypes, TX: $txBytes, RX: $rxBytes, DT: $datetime");

            // Define time conditions
            // $thirtyDaysAgo = now()->subDays(30);

            // Check if datetime is within the last 30 days
            // if ($datetime->greaterThanOrEqualTo($thirtyDaysAgo)) {
            //     DB::table('t_traffic_logs')->insert([
            //         'idrouter'  => $idRouter,
            //         'int_type'  => $intTypes,
            //         'tx_bytes'  => $txBytes,
            //         'rx_bytes'  => $rxBytes,
            //         'datetime'  => $datetime->toDateTimeString(),
            //         'timestamp' => now(),
            //     ]);
            // }

            // Check if datetime is exactly today
            // if ($datetime->isToday()) {
            //     DB::table('t_traffic_logs_daily')->insert([
            //         'idrouter'  => $idRouter,
            //         'int_type'  => $intTypes,
            //         'tx_bytes'  => $txBytes,
            //         'rx_bytes'  => $rxBytes,
            //         'datetime'  => $datetime,
            //         'timestamp' => now(),
            //     ]);
            // }

            DB::table('t_traffic_logs_daily')->insert([
                    'idrouter'  => $idRouter,
                    'int_type'  => $intTypes,
                    'tx_bytes'  => $txBytes,
                    'rx_bytes'  => $rxBytes,
                    'datetime'  => $datetime,
                    'timestamp' => now(),
                ]);

            return response()->json(['message' => 'Data received successfully'], 200);
        } catch (ValidationException $e) {
        return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error("Traffic Data Error: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong, please try again later'], 400);
        }
    }


}