<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr; // Add this at the top
use Carbon\Carbon;

class TrafficCollectorController extends Controller {
    public function index() {

    }

    // public function LEGACYcollectTrafficData(Request $request)
    // {
    //     try {
    //         // Define your secret key (store this securely in .env file)
    //         $secretKey = env('SECRET_KEY', 'default_secret');

    //         // Check if the secret key is provided before validation
    //         if ($request->input('key') !== $secretKey) {
    //             return response()->json(['error' => 'Unauthorized request'], 403);
    //         }

    //         // Validate the incoming request
    //         $validatedData = $request->validate([
    //             'idr'   => 'required|string', // Ensure it's a valid router ID
    //             'intp'  => 'required|string|max:255', // Port type, string with a max length
    //             'tx'    => 'required|integer|min:0', // TX bytes must be a positive integer
    //             'rx'    => 'required|integer|min:0', // RX bytes must be a positive integer
    //             'dt'    => 'required|string', // Ensure proper datetime format
    //         ]);

    //         // Extract validated data
    //         $idRouter  = $validatedData['idr'];
    //         $intTypes  = $validatedData['intp'];
    //         $txBytes   = $validatedData['tx'];
    //         $rxBytes   = $validatedData['rx'];
    //         // $dtInput  = $validatedData['dt']; // Convert to Carbon instance
    //         // $dtInput = ucwords(trim($validatedData['dt']));
    //         $dtInput = urldecode($validatedData['dt']);

    //         // ✅ Try parsing the datetime
    //         try {
    //             // $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput);
    //             $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput)->startOfDay();

    //             // ✅ Check if time is between 00:00:00 and 12:00:00
    //             if ($datetime->hour < 12) {
    //                 $datetime->subDay(); // Subtract one day
    //                 Log::info("Date adjusted to previous day and stored", ['adjusted_datetime' => $datetime->toDateTimeString()]);
    //             }

    //         } catch (\Exception $e) {
    //             Log::error("Invalid datetime format", [
    //                 'received' => $dtInput, 
    //                 'expected_format' => 'M/d/Y H:i:s'
    //             ]);
    //             return response()->json([
    //                 'error' => 'Invalid datetime format. Expected: mmm/DD/YYYY H:m:s',
    //                 'received' => $dtInput
    //             ], 422);
    //         }
            
    //         // Log data (optional)
    //         // Log::info("Traffic Data - ID_R: $idRouter, INT: $intTypes, TX: $txBytes, RX: $rxBytes, DT: $datetime");

    //         // Define time conditions
    //         // $thirtyDaysAgo = now()->subDays(30);

    //         // Check if datetime is within the last 30 days
    //         // if ($datetime->greaterThanOrEqualTo($thirtyDaysAgo)) {
                
    //         // }
            
    //         // Check if datetime is exactly today
    //         // if ($datetime->isToday()) {
    //         //     DB::table('t_traffic_logs_daily')->insert([
    //         //         'idrouter'  => $idRouter,
    //         //         'int_type'  => $intTypes,
    //         //         'tx_bytes'  => $txBytes,
    //         //         'rx_bytes'  => $rxBytes,
    //         //         'datetime'  => $datetime,
    //         //         'timestamp' => now(),
    //         //     ]);
    //         // }
    //         DB::table('t_traffic_logs')->insert([
    //                 'idrouter'  => $idRouter,
    //                 'int_type'  => $intTypes,
    //                 'tx_bytes'  => $txBytes,
    //                 'rx_bytes'  => $rxBytes,
    //                 'datetime'  => $datetime->toDateTimeString(),
    //                 'timestamp' => now(),
    //             ]);

    //         // DB::table('t_traffic_logs_daily')->insert([
    //         //         'idrouter'  => $idRouter,
    //         //         'int_type'  => $intTypes,
    //         //         'tx_bytes'  => $txBytes,
    //         //         'rx_bytes'  => $rxBytes,
    //         //         'datetime'  => $datetime->toDateTimeString(),
    //         //         'timestamp' => now(),
    //         //     ]);

    //         // return response()->json(['message' => 'Data received successfully'], 200);
    //         return response()->make("
    //             <h3 style='color: green;'>Data received successfully</h3>
    //         ", 200)->header('Content-Type', 'text/html');
    //         } 
    //         catch (ValidationException $e) {
    //         // return response()->json(['error' => $e->errors()], 422);
    //         return response()->make("
    //             <h3 style='color: red;'>Validation Error</h3>
    //             <p><strong>Errors:</strong> " . implode(', ', Arr::flatten($e->errors())) . "</p>
    //         ", 422)->header('Content-Type', 'text/html');
    //         } 
    //         catch (\Exception $e) {
    //             Log::error("Traffic Data Error: " . $e->getMessage());
    //             // return response()->json(['error' => 'Something went wrong, please try again later'], 400);
    //             // return response()->json(['error' => $e->getMessage()], 400);
    //             return response()->make("
    //                 <h3 style='color: red;'>Something went wrong</h3>
    //                 <p><strong>Error:</strong> " . htmlentities($e->getMessage()) . "</p>
    //             ", 400)->header('Content-Type', 'text/html');
    //         }
    // }

    public function collectTrafficData(Request $request) {
        try {
            // Define your secret key (store this securely in .env file)
            $secretKey = env('SECRET_KEY', 'default_secret');

            // Check if the secret key is provided before validation
            if ($request->input('key') !== $secretKey) {
                return response()->json(['error' => 'Unauthorized request'], 403);
            }

            // Validate the incoming request
            // $validatedData = $request->validate([
            //     'idr'   => 'required|string', // Ensure it's a valid router ID
            //     'intp'  => 'required|string|max:255', // Port type, string with a max length
            //     'tx'    => 'required|integer|min:0', // TX bytes must be a positive integer
            //     'rx'    => 'required|integer|min:0', // RX bytes must be a positive integer
            //     'dt'    => 'required|string', // Ensure proper datetime format
            // ]);

            // Extract validated data
            // $idRouter  = $validatedData['idr'];
            // $intTypes  = $validatedData['intp'];
            // $txBytes   = $validatedData['tx'];
            // $rxBytes   = $validatedData['rx'];
            // $dtInput = urldecode($validatedData['dt']);
            // Convert inputs to arrays if not already
            $idr = (array) $request->input('idr');
            $intp = (array) $request->input('intp');
            $tx = (array) $request->input('tx');
            $rx = (array) $request->input('rx');
            $dt = (array) $request->input('dt');

            // Validate input count consistency
            $count = count($idr);
            if ($count !== count($intp) || $count !== count($tx) || $count !== count($rx) || $count !== count($dt)) {
                return response()->json(['error' => 'Mismatched parameter counts'], 400);
            }

            // Validate input data
            $request->validate([
                'idr'   => 'required|array',
                'idr.*' => 'required|string',
                'intp'  => 'required|array',
                'intp.*'=> 'required|string|max:255',
                'tx'    => 'required|array',
                'tx.*'  => 'required|integer|min:0',
                'rx'    => 'required|array',
                'rx.*'  => 'required|integer|min:0',
                'dt'    => 'required|array',
                'dt.*'  => 'required|string',
            ]);

            $insertData = [];
                for ($i = 0; $i < $count; $i++) {
                    try {
                        $dtInput = urldecode($dt[$i]);
                        $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput);

                        // Adjust time if before 12:00 PM
                        if ($datetime->hour < 12) {
                            $datetime->subDay();
                        }

                        $datetime = $datetime->startOfDay();

                        $insertData[] = [
                            'idrouter'  => $idr[$i],
                            'int_type'  => $intp[$i],
                            'tx_bytes'  => $tx[$i],
                            'rx_bytes'  => $rx[$i],
                            'datetime'  => $datetime->toDateTimeString(),
                            'timestamp' => now(),
                        ];
                    } catch (\Exception $e) {
                        Log::error("Invalid datetime format", ['received' => $dtInput]);
                        return response()->json([
                            'error' => 'Invalid datetime format for entry ' . ($i + 1),
                            'received' => $dtInput
                        ], 422);
                    }
                }

                // Insert all data at once
                DB::table('t_traffic_logs')->insert($insertData);

                return response()->make("<h3 style='color: green;'>Data received successfully</h3>", 200)
                    ->header('Content-Type', 'text/html');

            } catch (ValidationException $e) {
                return response()->make("
                    <h3 style='color: red;'>Validation Error</h3>
                    <p><strong>Errors:</strong> " . implode(', ', Arr::flatten($e->errors())) . "</p>
                ", 422)->header('Content-Type', 'text/html');
            } catch (\Exception $e) {
                Log::error("Traffic Data Error: " . $e->getMessage());
                return response()->make("
                    <h3 style='color: red;'>Something went wrong</h3>
                    <p><strong>Error:</strong> " . htmlentities($e->getMessage()) . "</p>
                ", 400)->header('Content-Type', 'text/html');
            }


    }

    public function collectTrafficDataDaily(Request $request) {
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
            $dtInput = urldecode($validatedData['dt']);

            // ✅ Try parsing the datetime
            try {
                // $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput);
                $datetime = Carbon::createFromFormat('M/d/Y H:i:s', $dtInput);

                // ✅ Check if time is between 00:00:00 and 12:00:00
                if ($datetime->hour < 12) {
                    $datetime->subDay(); // Subtract one day
                    // Log::info("Date adjusted to previous day and stored", ['adjusted_datetime' => $datetime->toDateTimeString()]);
                }
                $datetime = $datetime->startOfDay();

            } catch (\Exception $e) {
                Log::error("Invalid datetime format", [
                    'received' => $dtInput, 
                    'expected_format' => 'M/d/Y H:i:s'
                ]);
                return response()->json([
                    'error' => 'Invalid datetime format. Expected: mmm/DD/YYYY H:m:s',
                    'received' => $dtInput
                ], 422);
            }

            DB::table('t_traffic_logs_daily')->insert([
                    'idrouter'  => $idRouter,
                    'int_type'  => $intTypes,
                    'tx_bytes'  => $txBytes,
                    'rx_bytes'  => $rxBytes,
                    'datetime'  => $datetime->toDateTimeString(),
                    'timestamp' => now(),
                ]);

            return response()->make("
                <h3 style='color: green;'>Data received successfully</h3>
            ", 200)->header('Content-Type', 'text/html');
            } 
            catch (ValidationException $e) {
            
            return response()->make("
                <h3 style='color: red;'>Validation Error</h3>
                <p><strong>Errors:</strong> " . implode(', ', Arr::flatten($e->errors())) . "</p>
            ", 422)->header('Content-Type', 'text/html');
            } 
            catch (\Exception $e) {
                Log::error("Traffic Data Error: " . $e->getMessage());
                
                return response()->make("
                    <h3 style='color: red;'>Something went wrong</h3>
                    <p><strong>Error:</strong> " . htmlentities($e->getMessage()) . "</p>
                ", 400)->header('Content-Type', 'text/html');
            }
    }


}