<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\{User, SupplyChainNetwork, Spedizionis, Transazionis, TransazioniHistory};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;
use App\Services\NadirByteCrypt;

class TransazioniController extends Controller
{
    use ApiResponser;

    public function getTransazionis()
    {
        $records = DB::table('transazionis')
            ->join('spedizionis', 'transazionis.prodotto', '=', 'spedizionis.id')
            ->join('supply_chain_networks', 'spedizionis.destinatario', '=', 'supply_chain_networks.id')
            ->join('users AS sender', 'spedizionis.user_id', '=', 'sender.id')
            ->join('users', 'supply_chain_networks.network_owner', '=', 'users.id')
            ->where('trasportatore', Auth::user()->id)
            ->select('transazionis.*', 'sender.nome AS sender', 'spedizionis.nome as nome_prodotto', 'spedizionis.id_sensore AS sensor_id', 'users.id as destinatarioId', 'users.nome as destinatarioNome', 'users.indirizzo as destinatarioIndirizzo')
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            foreach($records as $record){
                $record->nome_prodotto = NadirByteCrypt::decrypt($record->nome_prodotto);
                $record->sender = NadirByteCrypt::decrypt($record->sender);
                $record->destinatarioNome = NadirByteCrypt::decrypt($record->destinatarioNome);
            }
            return $this->success($records, 'Record found', 200);
        }
    }

    public function acceptTransazioni(Request $request)
    {
        // return Carbon::now()->format('d-m-Y H:i:s');
        $id = $request->id;
        $transazioni = Transazionis::find($id);
        $transazioni->stato = "In Transit";
        $transazioni->data_di_carico = $request->data_di_carico;

        $transazioni->update();

        $spedizionis = Spedizionis::find($transazioni->prodotto);
        $spedizionis->status = "In Transit";
        $spedizionis->update();


        $transazioniObj = DB::table('transazionis')
            ->join('spedizionis', 'transazionis.prodotto', '=', 'spedizionis.id')
            ->join('supply_chain_networks', 'spedizionis.destinatario', '=', 'supply_chain_networks.id')
            ->join('users AS sender', 'spedizionis.user_id', '=', 'sender.id')
            ->join('users', 'supply_chain_networks.network_owner', '=', 'users.id')
            ->where('transazionis.id', $transazioni->id)
            ->select('transazionis.*', 'sender.nome AS sender', 'spedizionis.nome as nome_prodotto', 'spedizionis.id_sensore AS sensor_id', 'users.id as destinatarioId', 'users.nome as destinatarioNome', 'users.indirizzo as destinatarioIndirizzo')
            ->first();

        
        $transazioniObj->nome_prodotto = NadirByteCrypt::decrypt($transazioniObj->nome_prodotto);
        $transazioniObj->sender = NadirByteCrypt::decrypt($transazioniObj->sender);
        $transazioniObj->destinatarioNome = NadirByteCrypt::decrypt($transazioniObj->destinatarioNome);
        
        return $this->success($transazioniObj, 'Transaction accepted!', 200);
    }

    public function rejectTransazioni($id)
    {
        $transazioni = Transazionis::find($id);

        $spedizioni = Spedizionis::find($transazioni->prodotto);
        $spedizioni->status = "Registered";
        $spedizioni->assigned_driver = null;
        $spedizioni->update();

        $transazioni->delete();
        return $this->success($spedizioni, 'Transaction rejected!', 200);
    }

    public function assignTransazioni(Request $request, $id)
    {
        $transazioni = Transazionis::find($id);

        if ($transazioni->type == 'Internal') {
            $transazioni->data_di_scarico = $request->data_di_scarico;
            $transazioni->stato = "Ended";
            $transazioni->update();

            if ($transazioni) {
                $newTransazioni = new Transazionis();
                $newTransazioni->prodotto = $transazioni->prodotto;
                $newTransazioni->trasportatore = $request->trasportatore;
                $newTransazioni->data_di_carico = $request->data_di_carico;
                $newTransazioni->stato = "In Transit";
                $newTransazioni->type = "Internal";
                $newTransazioni->save();
                $newTransazioni->codice = "T" . Str::random(6) . $newTransazioni->id;
                $newTransazioni->update();

                $history = new TransazioniHistory();
                $history->assigned_to = $newTransazioni->trasportatore;
                $history->assigned_by = Auth::user()->id;
                $history->assigned_transaction = $transazioni->id;
                $history->assigned_transaction_to = $newTransazioni->id;
                $history->prodotto = $newTransazioni->prodotto;
                $history->save();

                $prodotto = Spedizionis::find($newTransazioni->prodotto);
                $prodotto->assigned_driver = $newTransazioni->trasportatore;
                $prodotto->update();
            }

            $transazioniObj = DB::table('transazionis')
                ->join('spedizionis', 'transazionis.prodotto', '=', 'spedizionis.id')
                ->join('supply_chain_networks', 'spedizionis.destinatario', '=', 'supply_chain_networks.id')
                ->join('users AS sender', 'spedizionis.user_id', '=', 'sender.id')
                ->join('users', 'supply_chain_networks.network_owner', '=', 'users.id')
                ->where('transazionis.id', $transazioni->id)
                ->select('transazionis.*', 'sender.nome AS sender', 'spedizionis.nome as nome_prodotto', 'spedizionis.id_sensore AS sensor_id', 'users.id as destinatarioId', 'users.nome as destinatarioNome', 'users.indirizzo as destinatarioIndirizzo')
                ->first();

            $transazioniObj->nome_prodotto = NadirByteCrypt::decrypt($transazioniObj->nome_prodotto);
            $transazioniObj->sender = NadirByteCrypt::decrypt($transazioniObj->sender);
            $transazioniObj->destinatarioNome = NadirByteCrypt::decrypt($transazioniObj->destinatarioNome);
            
            return $this->success($transazioniObj, 'Transaction assigned!', 200);
        } else {

            $transazioni->data_di_scarico = $request->data_di_scarico;
            $transazioni->stato = "Ended";
            $transazioni->update();

            $prodotto = Spedizionis::find($transazioni->prodotto);
            $prodotto->status = "Ended";
            $prodotto->update();


            $transazioniObj = DB::table('transazionis')
                ->join('spedizionis', 'transazionis.prodotto', '=', 'spedizionis.id')
                ->join('supply_chain_networks', 'spedizionis.destinatario', '=', 'supply_chain_networks.id')
                ->join('users AS sender', 'spedizionis.user_id', '=', 'sender.id')
                ->join('users', 'supply_chain_networks.network_owner', '=', 'users.id')
                ->where('transazionis.id', $transazioni->id)
                ->select('transazionis.*', 'sender.nome AS sender', 'spedizionis.nome as nome_prodotto', 'spedizionis.id_sensore AS sensor_id', 'users.id as destinatarioId', 'users.nome as destinatarioNome', 'users.indirizzo as destinatarioIndirizzo')
                ->first();
            
            $transazioniObj->nome_prodotto = NadirByteCrypt::decrypt($transazioniObj->nome_prodotto);
            $transazioniObj->sender = NadirByteCrypt::decrypt($transazioniObj->sender);
            $transazioniObj->destinatarioNome = NadirByteCrypt::decrypt($transazioniObj->destinatarioNome);
        
            return $this->success($transazioniObj, 'Transaction assigned!', 200);
        }
    }
}
