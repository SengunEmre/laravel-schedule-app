<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\TextUI\Output\Printer;

class ScheduleController extends Controller
{
    public function fetchSchedule(Request $request)
    {
        $pol = $request->input('pol');
        $pod = $request->input('pod');

        // Path to the Node.js scripts
        $OneLinescriptPath = base_path('resources/js/OneRequest.js');
        $ArkasLinescriptPath = base_path('resources/js/arkasRequest.js');

        // Initialize the combined output array
        $combinedOutput = [
            'oneLineData' => null,
            'arkasLineData' => null,
        ];

        try {
            // Run the OneRequest.js script
            $OneLinecommand = "node $OneLinescriptPath $pol $pod";
            $OneLineoutput = shell_exec($OneLinecommand);
            Log::info('Raw output from OneRequest.js: ' . $OneLineoutput);

            // Decode the JSON output
            $OneLinejsonOutput = json_decode($OneLineoutput, true);

            // Check if the decoding was successful
            if (json_last_error() === JSON_ERROR_NONE) {
                $combinedOutput['oneLineData'] = $OneLinejsonOutput;
            } else {
                throw new \Exception('Failed to decode JSON output from OneRequest.js: ' . json_last_error_msg());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        try {
            // Run the arkasRequest.js script
            $ArkasLinecommand = "node $ArkasLinescriptPath $pol $pod";
            $ArkasLineoutput = shell_exec($ArkasLinecommand);
            Log::info('Raw output from arkasRequest.js: ' . $ArkasLineoutput);

            // Decode the JSON output
            $ArkasLinejsonOutput = json_decode($ArkasLineoutput, true);

            // Check if the decoding was successful
            if (json_last_error() === JSON_ERROR_NONE) {
                $combinedOutput['arkasLineData'] = $ArkasLinejsonOutput;
            } else {
                throw new \Exception('Failed to decode JSON output from arkasRequest.js: ' . json_last_error_msg());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        
        $combinedOutput = array_filter($combinedOutput, function($value) {
            return !empty($value);
        });
        // Return the output as JSON or handle the view as needed
        return view('schedule-results', ['data' => $combinedOutput]);
    }
}
