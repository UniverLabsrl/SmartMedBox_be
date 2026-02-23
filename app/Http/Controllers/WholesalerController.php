<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\{User, SupplyChainNetwork, Spedizionis, Transazionis, ProdottiDisponibili, TransazioniHistory, ChartsData, HumidityCoefficients};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DateTime;
use DateTimeZone;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class WholesalerController extends Controller
{
    use ApiResponser;

    public function getProductsByWholeSaler()
    {
        $networOwners =  SupplyChainNetwork::where('network_owner', Auth::user()->id)->select('id')->get();

        $arrSupply = [];
        foreach ($networOwners as $networOwner) {
            array_push($arrSupply, $networOwner->id);
        }

        $records = Spedizionis::whereIn('destinatario', $arrSupply)->with('user:id,nome')->select('id', 'nome', 'batch_number', 'units', 'bottle_capacity', 'qty', 'data_di_raccolto', 'assigned_driver','status', 'user_id', 'id_sensore')->get();

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

    public function getTransazioniHistory($id)
    {
        $finalArr = [];
        $spedizionis = Spedizionis::where('id', $id)->with('user:id,nome,indirizzo,citta,stato,cap')->select('id', 'nome', 'indirizzo', 'cap', 'data_di_raccolto', 'user_id', 'father_id')->first();

        $finalArr['prodotto'] = $spedizionis;

        // $user = User::where('id', $spedizionis->user_id)->select('id', 'nome', 'indirizzo', 'citta', 'stato', 'cap')->first();
        // $finalArr['user'] = $user;

        $historyTransazionis = TransazioniHistory::whereIn('prodotto', [$spedizionis->id, $spedizionis->father_id])->get();

        $historyArr = [];
        foreach ($historyTransazionis as $historyTransazioni) {
            if (!in_array($historyTransazioni->assigned_transaction,  $historyArr, true)) {
                array_push($historyArr, $historyTransazioni->assigned_transaction);
            }

            if (!in_array($historyTransazioni->assigned_transaction_to,  $historyArr, true)) {
                array_push($historyArr, $historyTransazioni->assigned_transaction_to);
            }
        }

        $transazionis = Transazionis::whereIn('id', $historyArr)->with('trasportatore:id,nome,cap,indirizzo')->select('id', 'trasportatore', 'data_di_carico', 'data_di_scarico')->get();

        $finalArr['transazioniHistory'] = $transazionis;

        return $this->success($finalArr, 'Record found', 200);
    }

    public function postChartsData(Request $request, $type, $shipment_id, $sensor_id)
    {

        // return 400 Bad Request if type is not 'temperature' or 'humidity'
        if (($type != 'temperature') && ($type != 'humidity')) {
            return response()->json([
                'status' => false,
                'message' => 'Type '.$type.' not available!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        $type = ucfirst($type);

        // find shipment from id
        $shipment = Spedizionis::find($shipment_id);
        if (!isset($shipment)) {
            return response()->json([
                'status' => false,
                'message' => 'Record not Found',
                'data' => [],
                'status_code' => 200
            ], 200);
        }

        // return 400 Bad Request if sensor_id received is not assigned to shipment_id received
        if ($shipment->id_sensore != $sensor_id) {
            return response()->json([
                'status' => false,
                'message' => 'Id sensor and id shipment not corresponding!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        // set timezone for received measure
        $tz = new DateTimeZone('Europe/Rome');

        // do not import measures before first transaction date
        $first_transaction = Transazionis::where('prodotto', $shipment->id)->orderBy('data_di_carico', 'ASC')->first();
        $last_time = DateTime::createFromFormat('d-m-Y H:i', $first_transaction->data_di_carico, $tz);

        // get time of last measure recorded for type, sensor and shipment
        $last_measure = ChartsData::where('type', $type)->where('sensor_id', $shipment->id_sensore)->where('shipment_id', $shipment->id)->orderBy('time', 'DESC')->first();
        if ($last_measure) {
            $last_measure_time = DateTime::createFromFormat('Y-m-d\TH:i:s.v', $last_measure->time, $tz);
            // set time of last measure as last date if it is after shipment creation date
            if ($last_measure_time && ($last_measure_time > $last_time)) {
                $last_time = $last_measure_time;
            }
        }

        // save received csv file as temporary file
        $csv = file_get_contents('php://input');
        if (strlen($csv) == 0) {
            return response()->json([
                'status' => false,
                'message' => 'File not found!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        $myfile = fopen('tmp.csv', 'w');
        fwrite($myfile, $csv);
        fclose($myfile);
        $measures = [];
        if (($handle = fopen('tmp.csv', 'r')) !== FALSE) {
            // read all rows in csv file
            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                if (count($data) < 2) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid .csv file found!',
                        'data' => [],
                        'status_code' => 400
                    ], 400);
                }
                // save measure if time is after last time
                $measure_time = DateTime::createFromFormat('Y-m-d\TH:i:s.v', $data[1], $tz);
                if ($measure_time && ($measure_time > $last_time)) {
                    $chartsData =  new ChartsData();
                    $chartsData->sensor_id = $shipment->id_sensore;
                    $chartsData->shipment_id = $shipment->id;
                    $chartsData->type = $type;
                    $chartsData->value = $data[0];
                    $chartsData->time = $data[1];
                    $chartsData->save();
                    array_push($measures, $chartsData);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'File not readable!',
                'data' => [],
                'status_code' => 400
            ], 400);
        }
        // remove temporary file
        unlink('tmp.csv');

        return $this->success($measures, 'Record created', 201);
    }
    
    public function getChartsData(Request $request)
    {
        $sensor_id = $request->sensor_id;
        $shipment_id = $request->shipment_id;
        $type = $request->type;

        $shipment = Spedizionis::find($shipment_id);
        if (!isset($shipment)) {
            return response()->json([
                'status' => false,
                'message' => 'Record not Found',
                'data' => [],
                'status_code' => 200
            ], 200);
        }

        $records = array();
        if ($shipment->father_id) {
            $father = Spedizionis::find($shipment->father_id);
            if ($father) {
                $records = array(ChartsData::where('type', $type)->where('sensor_id', $father->id_sensore)->where('shipment_id', $father->id)->orderBy('time', 'ASC')->get());
            }
        }
        $records = array_merge($records, array(ChartsData::where('type', $type)->where('sensor_id', $sensor_id)->where('shipment_id', $shipment_id)->orderBy('time', 'ASC')->get()));

        // Log::info("-------");
        // Log::info(array(ChartsData::where('type', $type)->where('sensor_id', $sensor_id)->where('shipment_id', $shipment_id)->orderBy('time', 'ASC')->toSql()));
        // Log::info($type);
        // Log::info($sensor_id);
        // Log::info($shipment_id);

        if ((count($records) == 0) || ((count($records) == 1) && (count($records[0]) == 0))) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {
            return $this->success($records[0], 'Record found', 200);
        }
    }

    public function getDeliveredProductsByWholeSaler()
    {
        $transazionis =  Transazionis::where('trasportatore', Auth::user()->id)->get();
        if ($transazionis->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data not available.',
                'data' => [],
                'status_code' => 200
            ], 200);
        } else {

            $prodottoArry = [];
            foreach ($transazionis  as $transazioni) {
                $shipment = Spedizionis::where('id', $transazioni->prodotto)->where('assigned_driver', Auth::user()->id)->with('user:id,nome')->first();
                if ($shipment) {
                    $shipment->transazioni_id = $transazioni->id;
                    $shipment->in_warehouse_since = $transazioni->data_di_carico;
                    $shipment->product = ProdottiDisponibili::where('id', $shipment->tipologia_di_prodotto)->first();

                    $now = new DateTime();

                    // get temperatures from sensor
                    $temperatures = array(ChartsData::where('type', 'Temperature')->where('sensor_id', $shipment->id_sensore)->where('shipment_id', $shipment->id)->orderBy('time', 'ASC')->get());

                    // get children
                    $children = Spedizionis::where('father_id', $shipment->id)->orderBy('batch_number', 'ASC')->get();
                    if ($children->isEmpty()) {
                        // set hour deltas for each temperature
                        $deltas = [];
                        $lastDate = new DateTime($shipment->data_di_raccolto);
                        $lastTemp = $shipment->temperatura_media;
                        if (count($temperatures) > 0) {
                            foreach ($temperatures[0] as $key => $temp) {
                                $thisDate = new DateTime($temp->time);
                                $diff = $thisDate->diff($lastDate);
                                $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                                array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $thisDate->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                                $lastTemp = number_format($temp->value, 2);
                                $lastDate = $thisDate;
                            }
                        }
                        // set last temperature delta to now
                        $diff = $now->diff($lastDate);
                        $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                        array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $now->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                        // set equivalent temperature
                        $shipment->equivalent_temperature = round($this->getEquivalentMeasure($deltas), 2);
                        
                        array_push($prodottoArry, $shipment);
                    } else {
                        foreach ($children as $child) {
                            $child->transazioni_id = $transazioni->id;
                            $child->in_warehouse_since = $transazioni->data_di_carico;
                            $child->product = $shipment->product;
                            $child->user = $shipment->user;

                            // get temperatures from sensor for child
                            $temperatures = array_merge($temperatures, array(ChartsData::where('type', 'Temperature')->where('sensor_id', $child->id_sensore)->where('shipment_id', $child->id)->orderBy('time', 'ASC')->get()));

                            // set hour deltas for each temperature
                            $deltas = [];
                            $lastDate = new DateTime($child->data_di_raccolto);
                            $lastTemp = $child->temperatura_media;
                            if (count($temperatures) > 0) {
                                foreach ($temperatures[0] as $key => $temp) {
                                    $thisDate = new DateTime($temp->time);
                                    $diff = $thisDate->diff($lastDate);
                                    $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                                    array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $thisDate->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                                    $lastTemp = number_format($temp->value, 2);
                                    $lastDate = $thisDate;
                                }
                            }
                            // set last temperature delta to now
                            $diff = $now->diff($lastDate);
                            $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                            array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $now->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                            // set equivalent temperature
                            $child->equivalent_temperature = round($this->getEquivalentMeasure($deltas), 2);
                            
                            array_push($prodottoArry, $child);
                        }
                    }
                }
            }

            return $this->success($prodottoArry, 'Record found', 200);
        }
    }

    public function calculateResidualShelfLife(Request $request)
    {
        //Log::info($request);
        $shipment_id = $request->shipment_id;
        $ids = $request->ids;
        $arrIds = preg_split ("/\,/", $ids);

        $shipment = Spedizionis::find($shipment_id);
        if ($shipment) {
            $product = ProdottiDisponibili::where('id', $shipment->tipologia_di_prodotto)->first();
            if ($product) {
                if ($product->algorithm_type == 1) {
                    if ($product->k1 == null) {
                        return response()->json([
                            'status' => false,
                            'message' => 'K1 parameter not setted for this product',
                            'data' => [],
                            'status_code' => 200
                        ], 200);
                    }
                    $k = $product->k1;
                } else if ($product->algorithm_type == 2) {
                    if ($product->k2 == null) {
                        return response()->json([
                            'status' => false,
                            'message' => 'K2 parameter not setted for this product',
                            'data' => [],
                            'status_code' => 200
                        ], 200);
                    }
                    $k = $product->k2;
                } else if ($product->algorithm_type == 3) {
                    if ($product->k3 == null) {
                        return response()->json([
                            'status' => false,
                            'message' => 'K3 parameter not setted for this product',
                            'data' => [],
                            'status_code' => 200
                        ], 200);
                    }
                    $k = $product->k3;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Algorithm type not setted for this product',
                        'data' => [],
                        'status_code' => 200
                    ], 200);
                }
                $product->humidity_coefficients = HumidityCoefficients::where('product_id', $product->id)->orderBy('from_humidity', 'ASC')->get();
                $shipment->product = $product;

                $now = new DateTime();
                $cropDate = new DateTime($shipment->data_di_raccolto);
                $diffFromCrop = $now->diff($cropDate);
                $fromCrop = ($diffFromCrop->y * 24 * 365) + ($diffFromCrop->m * 24 * 30) + ($diffFromCrop->d * 24) + $diffFromCrop->h + ($diffFromCrop->i/60) + ($diffFromCrop->s/3600);
                // days from crop
                $shipment->from_crop = $fromCrop/24;

                $temperatures = array();
                $humidities = array();
                if ($shipment->father_id) {
                    $father = Spedizionis::find($shipment->father_id);
                    if ($father) {
                        // get temperatures from sensor for father shipment
                        $temperatures = array(ChartsData::where('type', 'Temperature')->where('sensor_id', $father->id_sensore)->where('shipment_id', $father->id)->orderBy('time', 'ASC')->get());
                        // get humidities from sensor for father shipment
                        $humidities = array(ChartsData::where('type', 'Humidity')->where('sensor_id', $father->id_sensore)->where('shipment_id', $father->id)->orderBy('time', 'ASC')->get());
                    }
                }
                // get temperatures from sensor
                $temperatures = array_merge($temperatures, array(ChartsData::where('type', 'Temperature')->where('sensor_id', $shipment->id_sensore)->where('shipment_id', $shipment->id)->orderBy('time', 'ASC')->get()));

                // set hour deltas for each temperature
                $deltas = [];
                $lastDate = $cropDate;
                $lastTemp = $shipment->temperatura_media;
                if (count($temperatures) > 0) {
                    foreach ($temperatures[0] as $key => $temp) {
                        $thisDate = new DateTime($temp->time);
                        $diff = $thisDate->diff($lastDate);
                        $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                        array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $thisDate->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                        $lastTemp = number_format($temp->value, 2);
                        $lastDate = $thisDate;
                    }
                    // set last temperature delta to now
                    $diff = $now->diff($lastDate);
                    $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                    array_push($deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $now->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                    $fromCrop += $hourDelta;
                }
                // add temperatures received in request
                foreach ($arrIds as $i) {
                    $hourDelta = ($request->input('delta_'.$i) && ($request->input('delta_'.$i) != "null")) ? $request->input('delta_'.$i) : 24;
                    array_push($deltas, array( 'delta' => $hourDelta, 'value' => $request->input('temp_'.$i) ));
                    $fromCrop += $hourDelta;
                    $lastTemp = $request->input('temp_'.$i);
                }
                // get equivalent temperature
                $equivalent_temperature = round($this->getEquivalentMeasure($deltas), 2);

                // get humidities from sensor
                $humidities = array_merge($humidities, array(ChartsData::where('type', 'Humidity')->where('sensor_id', $shipment->id_sensore)->where('shipment_id', $shipment->id)->orderBy('time', 'ASC')->get()));

                // set hour deltas for each humidity
                $h_deltas = [];
                $first = true;
                if (count($humidities) > 0) {
                    foreach ($humidities[0] as $key => $hum) {
                        $thisDate = new DateTime($hum->time);
                        if ($first) {
                            $lastDate = $thisDate;
                            $first = false;
                        } else {
                            $diff = $thisDate-> diff($lastDate);
                            $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                            array_push($h_deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $thisDate->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                            $lastDate = $thisDate;
                        }
                        $lastTemp = number_format($hum->value, 2);
                    }
                    // set last humidity delta to now
                    $diff = $now->diff($lastDate);
                    $hourDelta = ($diff->y * 24 * 365) + ($diff->m * 24 * 30) + ($diff->d * 24) + $diff->h + ($diff->i/60) + ($diff->s/3600);
                    array_push($h_deltas, array( 'from' => $lastDate->format('Y-m-d H:i:s'), 'to' => $now->format('Y-m-d H:i:s'), 'delta' => $hourDelta, 'value' => $lastTemp ));
                }
                $shipment->humidities = $h_deltas;
                // get equivalent humidity
                $shipment->equivalent_humidity = round($this->getEquivalentMeasure($h_deltas), 2);

                $shelflives = [
                    'shelflife_1' => [
                        'days_from_crop' => $fromCrop/24,
                        'temperatures' => $deltas,
                        'equivalent_temperature' => $equivalent_temperature,
                        'shelflife' => ($product['reference_temperature_1'] && $product['shelflife_rt_1']) ? null : 'Missing parameters'
                    ],
                    'shelflife_2' => [
                        'days_from_crop' => $fromCrop/24,
                        'temperatures' => $deltas,
                        'equivalent_temperature' => $equivalent_temperature,
                        'shelflife' => ($product['reference_temperature_2'] && $product['shelflife_rt_2']) ? null : 'Missing parameters'
                    ],
                    'shelflife_3' => [
                        'days_from_crop' => $fromCrop/24,
                        'temperatures' => $deltas,
                        'equivalent_temperature' => $equivalent_temperature,
                        'shelflife' => ($product['reference_temperature_3'] && $product['shelflife_rt_3']) ? null : 'Missing parameters'
                    ]
                ];
                $count = 0;
                while ((($shelflives['shelflife_1']['shelflife'] == null) || ($shelflives['shelflife_2']['shelflife'] == null) || ($shelflives['shelflife_3']['shelflife'] == null))) {
                    for ($i = 1; $i <= 3; $i++) {
                        if ($shelflives['shelflife_'.$i]['shelflife'] != 'Missing parameters') {
                            $sl = $this->getShelfLife($product->algorithm_type, $product['shelflife_rt_'.$i], $shelflives['shelflife_'.$i]['equivalent_temperature'], $product['reference_temperature_'.$i], $k);
                            if (!$sl || is_infinite($sl) || is_nan($sl) || ($sl > $shelflives['shelflife_'.$i]['days_from_crop'])) {
                                array_push($shelflives['shelflife_'.$i]['temperatures'], array( 'delta' => 24, 'value' => $lastTemp ));
                                $shelflives['shelflife_'.$i]['equivalent_temperature'] = round($this->getEquivalentMeasure($shelflives['shelflife_'.$i]['temperatures']), 2);
                                $shelflives['shelflife_'.$i]['days_from_crop'] += 1;
                            } else {
                                $shelflives['shelflife_'.$i]['shelflife'] = $sl;
                            }
                        }
                    }
                    if ($count == 99) {
                        for ($i = 1; $i <= 3; $i++) {
                            if ($shelflives['shelflife_'.$i]['shelflife'] == null) {
                                $shelflives['shelflife_'.$i]['shelflife'] = 'Max iteration reached';
                            }
                        }
                    }
                    $count += 1;
                }
                $shipment->shelflives = $shelflives;

                $sl_sum = 0;
                $et_sum = 0;
                $sl_count = 0;
                for ($i = 1; $i <= 3; $i++) {
                    if (is_numeric($shelflives['shelflife_'.$i]['shelflife'])) {
                        $sl_sum += $shelflives['shelflife_'.$i]['shelflife'];
                        $et_sum += $shelflives['shelflife_'.$i]['equivalent_temperature'];
                        $sl_count += 1;
                    }
                }
                if ($sl_count == 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Could not calculate residual Shelf-Life, try to change parameters or temperature',
                        'data' => [],
                        'status_code' => 200
                    ], 200);
                }
                $shipment->equivalent_temperature = $et_sum/$sl_count;
                $shipment->average_shelflife = $sl_sum/$sl_count;

                // Get humidity coefficient for equivalent humidity
                $shipment->humidity_coefficient = 1;
                foreach ($product->humidity_coefficients as $coeff) {
                    if (($shipment->equivalent_humidity >= $coeff->from_humidity) && ($shipment->equivalent_humidity < $coeff->to_humidity)) {
                        $shipment->humidity_coefficient = $coeff->coefficient;
                        break;
                    }
                }
                $shipment->adjusted_shelflife = $shipment->average_shelflife * $shipment->humidity_coefficient;
                $diff = $shipment->adjusted_shelflife - $shipment->from_crop;
                $shipment->residual_shelflife = ($diff < 0) ? floor($diff) : ceil($diff);

                //Log::info($shipment);
                return $this->success($shipment, 'Record found', 200);
            }
            if (!isset($product)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not Found',
                    'data' => [],
                    'status_code' => 200
                ], 200);
            }
        }
        if (!isset($shipment)) {
            return response()->json([
                'status' => false,
                'message' => 'Record not Found',
                'data' => [],
                'status_code' => 200
            ], 200);
        }
    }

    public function createTransactionByWholesaler(Request $request)
    {
        $transazioni = new Transazionis();
        $transazioni->prodotto = $request->prodotto;
        $transazioni->trasportatore = $request->trasportatore;
        $transazioni->nome = $request->nome;
        $transazioni->indirizzo = $request->indirizzo;
        $transazioni->cap = $request->cap;
        $transazioni->comune = $request->comune;
        $transazioni->country = $request->country;
        $transazioni->type = $request->type;
        $transazioni->stato = $request->stato;
        $transazioni->data_di_carico = $request->data_di_carico;
        $transazioni->save();
        $transazioni->codice = "T" . Str::random(6) . $transazioni->id;
        $transazioni->update();


        $history = new TransazioniHistory();
        $history->assigned_to = $transazioni->trasportatore;
        $history->assigned_by = Auth::user()->id;
        $history->assigned_transaction = $request->transazioni_id;
        $history->assigned_transaction_to = $transazioni->id;
        $history->prodotto = $transazioni->prodotto;
        $history->save();

        $prodotto = Spedizionis::find($transazioni->prodotto);
        $prodotto->assigned_driver = $transazioni->trasportatore;
        $prodotto->update();

        return $this->success($transazioni, 'Transaction created!', 200);
    }

    /*
     * Get mean of the values weighed for delta: sum(delta * value)/sum(delta)
     */
    private function getEquivalentMeasure(Array $datas)
    {
        $num = 0;
        $den = 0;
        foreach ($datas as $key => $value) {
            $num += $value['delta'] * $value['value'];
            $den += $value['delta'];
        }

        return ($den != 0) ? $num/$den : 0;
    }

   /*
    * Get shelf life with given parameters
    * $algorithm_type: algorithm type chosen for product
    * $b: shelf life at reference temperature
    * $c: equivalent temperature
    * $d: reference temperature
    * $k: constant for the algorithm type chosen for product
    */
    private function getShelfLife($algorithm_type, $b, $c, $d, $k)
    {
        if ($algorithm_type == 1) {
            // x = (b * e^(k - (d * k)/c))^(c/d)
            if (($c != 0) && ($d != 0)) {
                $x = pow($b * exp($k - ($d * $k)/$c), $c/$d);
                if ($x > 0) {
                    return $x;
                }
            }
        } else if ($algorithm_type == 2) {
            // x = (b * e^(d*k/(d+273) - c*k/(d+273)))^(1/(c/(d+273) - d/(d+273) + 1))
            $den1 = $d + 273;
            if ($den1 != 0) {
                $den2 = $c/($den1) - $d/($den1) + 1;
                if ($den2 != 0) {
                    $x = pow($b * exp(($d * $k)/$den1 - ($c * $k)/$den1), 1/$den2);
                    if ($x > 0) {
                        return $x;
                    }
                }
            }
        } else if ($algorithm_type == 3) {
            // x1 = (sqrt((4 * b * c * k) - (4 * b * d * k) + 1) + (2 * b * c * k) - (2 * b * d * k) + 1)/(2 * ((b * c^2 * k^2) - (2 * b * c * d * k^2) + (b * d^2 * k^2))
            // x2 = (sqrt((4 * b * c * k) - (4 * b * d * k) + 1) - (2 * b * c * k) + (2 * b * d * k) - 1)/(2 * ((b * c^2 * k^2) * -1 + (2 * b * c * d * k^2) - (b * d^2 * k^2))
            $x1 = null;
            $den = (2 * (($b * pow($c, 2) * pow($k, 2)) - (2 * $b * $c * $d * pow($k, 2)) + ($b * pow($d, 2) * pow($k, 2))));
            if ($den != 0) {
                $x1 = (sqrt((4 * $b * $c * $k) - (4 * $b * $d * $k) + 1) + (2 * $b * $c * $k) - (2 * $b * $d * $k) + 1)/$den;
            }
            $x2 = null;
            $den = (2 * ((($b * pow($c, 2) * pow($k, 2)) * -1) + (2 * $b * $c * $d * pow($k, 2)) - ($b * pow($d, 2) * pow($k, 2))));
            if ($den != 0) {
                $x2 = (sqrt((4 * $b * $c * $k) - (4 * $b * $d * $k) + 1) - (2 * $b * $c * $k) + (2 * $b * $d * $k) - 1)/$den;
            }
            if ($x1 && ($x1 > 0) && !$x2) {
                // return x1 if x1 is positive and x2 is null
                return $x1;
            } else if (!$x1 && $x2 && ($x2 > 0)) {
                // return x2 if x1 is null and x2 is positive 
                return $x2;
            } else if (($x1 > 0) && ($x2 > 0)) {
                // return mean of x1 and x2 if both are positive
                return ($x1 + $x2)/2;
            } else if (($x1 > 0) && ($x2 <= 0)) {
                // return x1 if x1 is positive and x2 is negative
                return $x1;
            } else {
                // return x2 if x1 is negative and x2 is positive
                return $x2;
            }
        }
    }
}
