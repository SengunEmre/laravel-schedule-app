<?php

namespace App\Http\Controllers;
use App\Models\Port; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortNameController extends Controller
{
    public function searchPortNames(Request $request)
    {
        $query = $request->input('q');
        $ports = Port::where('Name', 'LIKE', $query . '%')->get(['Id', 'name' , 'code']);
        return response()->json($ports);
    }

}
