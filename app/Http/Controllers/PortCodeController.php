<?php

namespace App\Http\Controllers;
use App\Models\Port; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortCodeController extends Controller
{
    public function getPortCode(Request $request)
    {
        $fromPort = $request->input('fromPort');
        $toPort = $request->input('toPort');
    
        $fromPortCode = Port::where('name', $fromPort)->value('code');
        $toPortCode = Port::where('name', $toPort)->value('code');
    
        return response()->json([
            'fromPortCode' => $fromPortCode,
            'toPortCode' => $toPortCode,
        ]);
    }
}
