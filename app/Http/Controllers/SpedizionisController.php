<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\{User, SupplyChainNetwork, Spedizionis, Transazionis, ProdottiDisponibili, HumidityCoefficients,ShelfLifeTrainingData};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\WholesalerController;
use Symfony\Component\Process\Process;

class SpedizionisController extends Controller
{
    use ApiResponser;

    public function getProdottisFromTrick()
    {
        if (env('TRICK_API_URL')) {
            $response = Http::withToken(Auth::user()->token_trick)->get(env('TRICK_API_URL').'food/entities/data/product/type');
            if ($response->unauthorized()) {
                return $this->error('Unauthorized.', ['error' => 'Unauthorized'], 401);
            } else if ($response->successful()) {
                return $this->success($response->collect(), 'Trick record found', 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not available.',
                    'data' => [],
                    'status_code' => 400
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        }
    }

    public function getProdottis()
    {
        $products = ProdottiDisponibili::orderBy('id', 'DESC')->get();
        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            foreach ($products as $product) {
                $product->humidity_coefficients = HumidityCoefficients::where('product_id', $product->id)->orderBy('from_humidity', 'ASC')->get();
            }
            return $this->success($products, 'Record found', 200);
        }
    }

    public function createProduct(Request $request)
    {
        if (!$request->nome_prodotto ) {
            return response()->json([
                'status' => false,
                'message' => 'Data missing to complete request!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        $product =  new ProdottiDisponibili();
        $this->populateProduct($request, $product);
        $product->save();

        //$this->updateHumidityCoefficients($product, $request->humidity_coefficients, null);

        return $this->success($product, 'Product created', 201);
    }

    public function updateProduct(Request $request, $id)
    {
        if (!$id || !$request->nome_prodotto || !$request->algorithm_type || !$request->reference_temperature_1 || !$request->shelflife_rt_1) {
            return response()->json([
                'status' => false,
                'message' => 'Data missing to complete request!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        $old = ProdottiDisponibili::find($id);
        if (!isset($old)) {
            return response()->json([
                'status' => false,
                'message' => 'Record not Found',
                'data' => [],
                'status_code' => 404
            ], 404);
        }
        $old->humidity_coefficients = HumidityCoefficients::where('product_id', $old->id)->orderBy('from_humidity', 'ASC')->get();

        $product =  new ProdottiDisponibili();
        $product->id = $id;
        $this->populateProduct($request, $product);
        $product->update();

        $this->updateHumidityCoefficients($product, $request->humidity_coefficients, $old->humidity_coefficients);

        return $this->success($product, 'Product updated', 200);
    }

    public function deleteProduct($id)
    {
        $product = ProdottiDisponibili::find($id);
        if (!isset($product)) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
                'data' => [],
                'status_code' => 404
            ], 404);
        }

        // delete humidity coefficients for product
        $product->humidity_coefficients = HumidityCoefficients::where('product_id', $product->id)->orderBy('from_humidity', 'ASC')->get();
        foreach ($product->humidity_coefficients as $coeff) {
            $coeff->delete();
        }

        // delete product
        $product->delete();

        return $this->success('Product deleted successfully', 200);
    }

    public function getBatchesFromTrick()
    {
        if (env('TRICK_API_URL')) {
            $response = Http::withToken(Auth::user()->token_trick)->get(env('TRICK_API_URL').'food/entities/data/product');
            if ($response->unauthorized()) {
                return $this->error('Unauthorized.', ['error' => 'Unauthorized'], 401);
            } else if ($response->successful()) {
                return $this->success($response->collect(), 'Trick record found', 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not available.',
                    'data' => [],
                    'status_code' => 400
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        }
    }

    public function getSpedizionis()
    {
        $shipments = Spedizionis::where('user_id', Auth::user()->id)->with('tipologia_di_prodotto')->with('destinatario')->orderBy('id', 'DESC')->get();
        if ($shipments->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            foreach ($shipments as $shipment) {
                $shipment->transactions = Transazionis::where('prodotto', $shipment->id)->orderBy('data_di_carico', 'ASC')->get();
            }
            return $this->success($shipments, 'Record found', 200);
        }
    }

    public function createShipment(Request $request)
    {
        $spedizionis = new Spedizionis();
        $spedizionis->user_id = Auth::user()->id;
        $spedizionis->nome = $request->nome;
        $spedizionis->father_id = $request->father_id;
        $spedizionis->batch_number = $request->batch_number;
        $spedizionis->units = $request->units;
        $spedizionis->bottle_capacity = $request->bottle_capacity;
        $spedizionis->qty = $request->qty;
        $spedizionis->size = $request->size;
        $spedizionis->category = $request->category;
        $spedizionis->tipologia_di_prodotto = $request->tipologia_di_prodotto;
        $spedizionis->tipologia_di_imballaggio = $request->tipologia_di_imballaggio;
        $spedizionis->id_sensore = $request->id_sensore;
        $spedizionis->descrizione_del_prodotto = $request->descrizione_del_prodotto;
        $spedizionis->data_di_raccolto = $request->data_di_raccolto;
        $spedizionis->indirizzo = $request->indirizzo;
        $spedizionis->cap = $request->cap;
        $spedizionis->citta = $request->citta;
        $spedizionis->stato = $request->stato;
        $spedizionis->destinatario = $request->destinatario;
        $spedizionis->temperatura_media = $request->temperatura_media;
        $spedizionis->status = $request->status;
        $spedizionis->save();

        if ($request->father_id) {
            $father = Spedizionis::find($request->father_id);
            $father->status = "Ended";
            $father->update();
        }

        $objSpedizionis = Spedizionis::where('id', $spedizionis->id)->with('tipologia_di_prodotto')->with('destinatario')->first();
        return $this->success($objSpedizionis, 'Record Save Successfully!', 201);
    }

    public function deleteShipment($id)
    {
        $record = Spedizionis::find($id);

        if ($record) {
            $record->delete();
            return $this->success('Record Delete Successfully', 200);
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

    public function assignTransporter(Request $request)
    {
        if ($request->type == "create") {
            $spedizioni = Spedizionis::find($request->spedizioni_id);
            $spedizioni->assigned_driver = $request->trasportatore;
            $spedizioni->status = "Pending";
            $spedizioni->update();

            $transazioni = new Transazionis();
            $transazioni->prodotto = $request->spedizioni_id;
            $transazioni->trasportatore = $request->trasportatore;
            $transazioni->stato = "Pending";
            $transazioni->type = "Internal";
            $transazioni->save();
            $transazioni->codice = "T" . Str::random(6) . $transazioni->id;
            $transazioni->update();

            $objSpedizioni = Spedizionis::where('id', $spedizioni->id)->with('tipologia_di_prodotto')->with('destinatario')->first();
            return $this->success($objSpedizioni, 'Record Save Successfully!', 201);
        }

        if ($request->type == 'edit') {
            $findTransazioni = Transazionis::where('prodotto', $request->spedizioni_id)->first();

            $findDriver = Spedizionis::where('assigned_driver', $findTransazioni->trasportatore)->first();

            if ($findTransazioni->trasportatore == $findDriver->assigned_driver) {
                $findTransazioni->delete();

                $spedizioni = Spedizionis::find($request->spedizioni_id);
                $spedizioni->assigned_driver = $request->trasportatore;
                $spedizioni->status = "Pending";
                $spedizioni->update();

                $transazioni = new Transazionis();
                $transazioni->prodotto = $request->spedizioni_id;
                $transazioni->trasportatore = $request->trasportatore;
                $transazioni->stato = "Pending";
                $transazioni->type = "Internal";
                $transazioni->save();
                $transazioni->codice = "T" . Str::random(6) . $transazioni->id;
                $transazioni->update();

                $objSpedizioni = Spedizionis::where('id', $spedizioni->id)->with('tipologia_di_prodotto')->with('destinatario')->first();
                return $this->success($objSpedizioni, 'Record Save Successfully!', 201);
            }
        }
    }

    private function populateProduct(Request $request, ProdottiDisponibili $product)
    {
        $product->user_id = Auth::user()->id;
        $product->nome_prodotto = $request->nome_prodotto;
        $product->algorithm_type = $request->algorithm_type;
        $product->reference_temperature_1 = $request->reference_temperature_1;
        $product->shelflife_rt_1 = $request->shelflife_rt_1;
        $product->reference_temperature_2 = $request->reference_temperature_2;
        $product->shelflife_rt_2 = $request->shelflife_rt_2;
        $product->reference_temperature_3 = $request->reference_temperature_3;
        $product->shelflife_rt_3 = $request->shelflife_rt_3;
        $product->k1 = $request->k1;
        $product->k2 = $request->k2;
        $product->k3 = $request->k3;
    }

    private function updateHumidityCoefficients(ProdottiDisponibili $product, $coefficients, $old_coefficients) {
        
        if ($old_coefficients) {
            foreach ($old_coefficients as $coeff) {
                $coeff->delete();
            }
        }
        foreach ($coefficients as $coeff) {
            $humidityCoeff = new HumidityCoefficients();
            $humidityCoeff->product_id = $product->id;
            $humidityCoeff->from_humidity = $coeff['from_humidity'];
            $humidityCoeff->to_humidity = $coeff['to_humidity'];
            $humidityCoeff->coefficient = $coeff['coefficient'];
            $humidityCoeff->save();
        }

        $product->humidity_coefficients = $coefficients;
    }

    public function setManualExpirationDate(Request $request){
        try {

            Log::info($request);


            $wholesalerController = app(WholesalerController::class);
            $shelflifeResult = $wholesalerController->calculateResidualShelfLife($request);

            $data = json_decode($shelflifeResult->getContent(), true)['data'];
            Log::info($data);
            $trainingRecord = new ShelfLifeTrainingData();
            $trainingRecord->shipment_id = $data['id'];
            $trainingRecord->product_type = $data['tipologia_di_prodotto'];
            $trainingRecord->temp_eq = $data['equivalent_temperature'];
            $trainingRecord->hum_eq = $data['equivalent_humidity'];
            $trainingRecord->formula_type = $data['product']['algorithm_type'];
            $trainingRecord->formula_result = $data['average_shelflife'];
            

            $pickup_date = new \DateTime($data['data_di_raccolto']);
            $expired_at = new \DateTime($this->convertDate($request->date));
            $diffInSeconds =  $expired_at->getTimestamp() - $pickup_date->getTimestamp();
            $diffInDays = $diffInSeconds / (60 * 60 * 24);

            $trainingRecord->actual_result = $diffInDays;
            $trainingRecord->save();

            //Log::info($trainingRecord);

            $shipment = Spedizionis::find($request->shipment_id);
            
            $cleanDate = preg_replace('/\s*\(.*\)$/', '', $request->date);
            $date = new \DateTime($cleanDate);
            $formattedDate = $date->format('Y-m-d H:i:s');

            $shipment->expired_at = $formattedDate;
            
            $shipment->save();

            return response()->json([
                'status' => true,
                'message' => 'Date saved',
                'data' => [$trainingRecord],
                'status_code' => 200
            ], 200);
            
            
        } 

        catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Errore',
                'data' => $e,
                'status_code' => 500
            ], 500);
        }
    }

    private function convertDate($jsDate){
        $cleaned = preg_replace('/\s*\(.*\)$/', '', $jsDate);
        $date = \DateTime::createFromFormat('D M d Y H:i:s T', $cleaned);

        if (!$date) {
            $cleaned = str_replace('GMT', '', $cleaned); // 'Wed Jul 02 2025 10:26:50 +0200'
            $date = \DateTime::createFromFormat('D M d Y H:i:s O', $cleaned);
        }

        if ($date) {
            return $date->format('Y-m-d H:i:s');
        } else {
            return false;
        }   
    }

    public function getAIPrediction(Request $request)
    {
        
        $request->validate([
            'sensor_id'   => 'required',
            'product_type'=> 'required'
        ]);
        Log::info('entro nel metof');

        $payload = [
            'sensor_id' => $request->sensor_id,
            'product_type' => $request->product_type
        ];

        
        $python = base_path('public/AI/venv/bin/python');
        $script = base_path('public/AI/scripts/predict_shelf_life.py');

        $process = new Process([$python, $script]);
        $process->setInput(json_encode($payload));
        $process->setTimeout(120);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json([
                'error' => 'Errore durante la predizione AI',
                'details' => $process->getErrorOutput()
            ], 500);
        }
        Log::info($process->getOutput());
        return response()->json([
            'status' => true,
            'data' => json_decode($process->getOutput(), true)
        ]);
    // //Shelflife calcolata tramite formula
        // $wholesalerController = app(WholesalerController::class);
        // $shelflifeResult = $wholesalerController->calculateResidualShelfLife($request);

        // $data = json_decode($shelflifeResult->getContent(), true)['data'];

        // $dataForPython = [
        //     'product_type' => $data['tipologia_di_prodotto'],
        //     'formula_type' => $data['product']['algorithm_type'],
        //     'temp_eq' => $data['equivalent_temperature'],
        //     'hum_eq' => $data['equivalent_humidity'],
        //     'formula_result' => $data['adjusted_shelflife'],
        // ];
        // Log::info($dataForPython);


        // $python = '/Users/gianluca/Documents/Repository/ShelflifeAI-BE/public/AI/venv/bin/python';
        // $script = '/Users/gianluca/Documents/Repository/ShelflifeAI-BE/public/AI/scripts/predict_shelf_life.py';

        // $process = new Process([$python, $script]);
        // $process->setInput(json_encode($dataForPython));
        // $process->setTimeout(1200);
        // $process->run();
        // try {
        //         $process->mustRun();
        //         Log::info( $process->getOutput());
        //     } catch (ProcessFailedException $exception) {
        //         Log::info( $exception->getMessage());
        //         Log::info( $process->getErrorOutput());
        //         return response()->json([
        //             'error' => 'Errore durante l\'esecuzione dello script',
        //             'details' => $process->getErrorOutput(). ' ' .$exception->getMessage()
        //         ], 500);
        //     }
        

        // if (!$process->isSuccessful()) {
        //     return response()->json([
        //         'error' => 'Errore durante l\'esecuzione dello script',
        //         'details' => $process->getErrorOutput()
        //     ], 500);
        // }

        // Log::info($process->getOutput());
        

        // $return = [
        //     'formula_result' => $data['adjusted_shelflife'],
        //     'predicted_shelf_life' =>  json_decode($process->getOutput(), true)['predicted_shelf_life']
        // ];


        // Log::info($dataForPython);
          
        
        // return response()->json([
        //     'status' => true,
        //     'message' => 'Shelflife calculated',
        //     'data' => [$return],
        //     'status_code' => 200
        // ], 200);
    }
}
