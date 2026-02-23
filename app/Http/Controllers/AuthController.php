<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Models\{User, SupplyChainNetwork};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Validator;
use Hash;
use Illuminate\Support\Str;
use Mail;

class AuthController extends Controller
{
    use ApiResponser;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error("L'e-mail esiste già.", $validator->errors(), 200);
        }

        $user = new User();
        $user->nome = $request->nome;
        $user->indirizzo = $request->indirizzo;
        $user->cap = $request->cap;
        $user->citta = $request->citta;
        $user->stato = $request->stato;
        $user->email = $request->email;
        $user->terms = $request->terms;
        $user->role = $request->role;

        $random_password = Str::random(6);
        $details = [
            'password' => $random_password,
            'nome' => $user->nome,
            'email' => $user->email,
        ];

        $adminEmail = env('ADMIN_EMAIL');
        \Mail::to($adminEmail)->send(new \App\Mail\WelcomeMail($details));

        if (Mail::failures()) {
            return $this->error('Not Send', ['error' => 'Sorry! Please try again latter'], 200);
        } else {
            $user->password = bcrypt($random_password);
            $user->save();
            if ($user->role == 'Wholesaler') {
                $user->codice = $request->codice . '' . $user->id;
                $user->update();
            }

            if (isset($request->codici_filiera)) {
                if ($request->role != 'Wholesaler') {
                    foreach ($request->codici_filiera as $codici) {
                        $findOwner = User::where('codice', $codici)->first();
                        if ($findOwner) {
                            $supplyChainNetwork =  new SupplyChainNetwork();
                            $supplyChainNetwork->network_owner = $findOwner->id;
                            $supplyChainNetwork->network_user = $user->id;
                            $supplyChainNetwork->status = "Active";
                            $supplyChainNetwork->save();
                        }
                    }
                }
            }

            // $user['token'] = $user->createToken('API Token')->plainTextToken;
            return $this->success($user, 'User register successfully.');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return $this->error('Bad request.', $validator->errors(), 400);
        }

        if (env('TRICK_API_URL')) { 
            $response = Http::post(env('TRICK_API_URL').'security/users/login', ['email' => $request->email, 'password' => $request->password]);
            if ($response->successful()) {
                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    $user = new User();
                }
                $user->nome = $response->json('lastName', '').' '.$response->json('firstName', '');
                $user->email = $response->json('email', '');
                $user->indirizzo = $response->json('address', '');
                $user->cap = $response->json('postalcode', '');
                $user->citta = $response->json('city', '');
                $user->stato = $response->json('country', '');
                $user->role = ($response->json('roles', '')[0] == 'isconsumer') ? 'Admin' : (($response->json('roles', '')[0] == 'issupplier') ? 'Wholesaler' : (($response->json('roles', '')[0] == 'issubcontractor') ? 'Trasportatore' : (($response->json('roles', '')[0] == 'isproducer') ? 'Produttore' : 'Wholesaler')));
                $user->password = bcrypt($request->password);
                $user->token_trick = $response->json('token', '');
                $user->save();

                if ($user->role == 'Wholesaler') {
                    $user->codice = $user->id;
                    $user->update();
                }

                $user->token = $user->createToken('API Token')->plainTextToken;

                return $this->success($user, 'User login successfully.');
            } else {
                return $this->error('Unauthorized.', ['error' => 'Unauthorized'], 401);
            }
        } else {
               
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $user['token'] = $user->createToken('API Token')->plainTextToken;

                return $this->success($user, 'User login successfully.');
            } else {
                return $this->error('Unauthorized.', ['error' => 'Unauthorized'], 200);
            }
        }
    }

    public function checkTrickUrl()
    {
        return [
            'message' => env('TRICK_API_URL')
        ];
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logout successfully'
        ];
    }

    public function forgetPassword(Request $request)
    {
        $input = $request->only('email');
        $validator = Validator::make($input, [
            'email' => "required|email"
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 200);
        }

        $find_user =  User::where('email', $input)->first();
        if (!$find_user) {
            return response()->json([
                'status' => false,
                'message' => 'There is not email found',
                'data' => 'Email not found',
                'status_code' => 200
            ], 200);
        } else {
            $random_password = Str::random(6);
            $details = [
                'password' => $random_password,
                'name' => $find_user->name,
            ];


            \Mail::to($find_user->email)->send(new \App\Mail\ForgetMail($details));

            if (Mail::failures()) {
                return $this->error('Not Send', ['error' => 'Sorry! Please try again latter'], 200);
            } else {
                $find_user->password = bcrypt($random_password);
                $find_user->save();
                return $this->success('Great! Successfully send in your mail');
            }
        }
    }

    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return $this->error('Bad request.', $validator->errors(), 400);
        }

        if (!(Hash::check($request->get('old_password'), Auth::user()->password))) {
            // The passwords matches
            return $this->error("Wrong password.", ['error' => 'Wrong password.'], 200);
        }
        if (strcmp($request->get('old_password'), $request->get('new_password')) == 0) {
            // Current password and new password same
            return $this->error("New password cannot be equal to the current password.", ['error' => 'New password cannot be equal to the current password.'], 200);
        }

        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new_password'));
        $user->save();

        return $this->success($user, "Password successfully changed!");
    }
}