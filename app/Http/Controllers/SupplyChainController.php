<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\{User, SupplyChainNetwork};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SupplyChainController extends Controller
{
    use ApiResponser;

    public function checkCodice($codice)
    {
        $checkCode = User::where('codice', $codice)->first();

        if ($checkCode) {
            return response()->json([
                'status' => true,
                'message' => 'Found',
                'data' => 'Record Found',
                'status_code' => 200
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a valid suplly chain code.',
                'data' => 'Record not Found',
                'status_code' => 200
            ], 200);
        }
    }

    public function getNetwork()
    {
        $records = SupplyChainNetwork::where('network_user', Auth::user()->id)->with('network_owner:id,nome,email,codice,created_at')->get();
        if ($records->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            return $this->success($records, 'Record found', 200);
        }
    }

    public function getNetworkStatus($status)
    {
        $records = SupplyChainNetwork::where('network_user', Auth::user()->id)->where('status', $status)->with('network_owner:id,nome')->get();
        if ($records->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            return $this->success($records, 'Record found', 200);
        }
    }

    public function getNetworkByOwner(Request $request)
    {
        $owner_id = $request->id;

        $records = SupplyChainNetwork::where('network_owner', $owner_id)
            ->with('network_user')->whereHas('network_user', function ($query) {
                $query->where('role', '=', 'Trasportatore')->where('id', '!=', Auth::user()->id)->select('id', 'nome', 'role');
            })
            ->with('network_user', function ($query) {
                $query->select('id', 'nome', 'role');
            })
            ->get();


        if ($request->owner == 'true') {
            $owner = User::where('id', $owner_id)->select('id', 'nome', 'role')->first();
            $records->push($owner);
        }

        if ($records->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No data found.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            return $this->success($records, 'Record found', 200);
        }
    }

    public function changeFiliereStatus($id)
    {
        $record = SupplyChainNetwork::find($id);
        if ($record) {
            $record->status = 'Not Active';
            $record->save();

            $objFiliere = SupplyChainNetwork::where('id', $record->id)->with('network_owner:id,nome,email,codice,created_at')->first();
            return $this->success($objFiliere, 'Record found', 200);
        }
        if (!isset($record)) {
            return response()->json([
                'status' => false,
                'message' => 'Record not Found',
                'data' => [],
                'status_code' => 200
            ], 200);
        }
    }

    public function createFiliere(Request $request)
    {
        $findNetworkOwner = User::where('codice', $request->codice)->first();

        if ($findNetworkOwner) {
            $checkNetwork = SupplyChainNetwork::where('network_owner', $findNetworkOwner->id)->where('network_user', Auth::user()->id)->first();
            if ($checkNetwork) {
                return response()->json([
                    'status' => false,
                    'message' => 'This supply chain code already exists.',
                    'data' => 'Already Exist.',
                    'status_code' => 200
                ], 200);
            } else {
                $filiere =  new SupplyChainNetwork();
                $filiere->network_owner = $findNetworkOwner->id;
                $filiere->network_user = Auth::user()->id;
                $filiere->status = "Active";
                $filiere->save();

                $objFiliere = SupplyChainNetwork::where('id', $filiere->id)->with('network_owner:id,nome,email,codice,created_at')->first();
                return $this->success($objFiliere, 'Supply chain code added successfully.', 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a valid suplly chain code.',
                'data' => 'Record not Found',
                'status_code' => 200
            ], 200);
        }
    }
}
