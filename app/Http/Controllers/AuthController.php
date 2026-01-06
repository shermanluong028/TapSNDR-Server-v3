<?php
namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        $host = $request->getHost();

        $mapRolesToHost = [
            env('APP_ADMIN_HOST', 'admin.tapsndr.com')   => ['master_admin', 'distributor'],
            env('APP_CLIENT_HOST', 'client.tapsndr.com') => ['user'],
            env('APP_REDEEM_HOST', 'redeem.tapsndr.com') => ['player'],
        ];

        if (! isset($mapRolesToHost[$host])) {
            return response('Not Found', 404);
        }

        $payload = $request->post();

        // TODO: Set default messages of Validator
        $validator = Validator::make($payload, [
            'username' => 'required',
            // 'password' => 'required|min:6',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        $user = User::where(function ($query) use ($payload) {
            $query
                ->where('username', $payload['username'])
                ->orWhere('email', $payload['username']);
        })->whereHas('roles', function ($query) use ($mapRolesToHost, $host) {
            $query->whereIn('name', $mapRolesToHost[$host]);
        })->first();

        if ($user && Auth::attempt(['id' => $user->id, 'password' => $payload['password']])) {
            $request->session()->regenerate();
            // try {
            //     Telegram::sendMessage([
            //         'chat_id' => env('TELEGRAM_CHAT_ID'),
            //         'text'    => $payload['username'] . ' signed in',
            //     ]);
            // } catch (\Throwable $th) {
            // }
            $user->activity_logs()->create([
                'activity_type' => 'login',
                'ip_address'    => $request->ip(),
                'country'       => Location::get($request->ip())->countryName ?? null,
                'user_agent'    => $request->userAgent(),
            ]);
            return Utils::responseData();
        }

        if ($host === env('APP_REDEEM_HOST', 'redeem.tapsndr.com')) {
            try {
                $response = Http::post('https://taparcadia.com:3000/api/login', [
                    'email'     => $payload['username'],
                    'password'  => $payload['password'],
                    'plateform' => '1',
                ]);
                $resData = $response->json();
                if ($resData && $resData['status']) {
                    $taparcadiaUser = $resData['data']['user'];
                    $user           = User::where('email', $taparcadiaUser['email'])->first();
                    if (! $user) {
                        $user = User::create([
                            'email' => $taparcadiaUser['email'],
                        ]);
                        $role = Role::where('name', 'player')->first();
                        $user->roles()->sync([$role->id]);
                    }
                    Auth::login($user);
                    $request->session()->regenerate();
                    $user->activity_logs()->create([
                        'activity_type' => 'login',
                        'ip_address'    => $request->ip(),
                        'country'       => Location::get($request->ip())->countryName ?? null,
                        'user_agent'    => $request->userAgent(),
                    ]);
                    return Utils::responseData();
                }
            } catch (\Throwable $th) {
                Log::error($th);
                return Utils::responseError(trans('extra.internal_error'));
            }

            // try {
            //     Telegram::sendMessage([
            //         'chat_id' => env('TELEGRAM_CHAT_ID'),
            //         'text'    => $payload['username'] . ' tried to sign in',
            //     ]);
            // } catch (\Throwable $th) {
            // }
        }

        return Utils::responseError(trans('extra.invalid_credential'));
    }

    public function signUp(Request $request)
    {
        $payload = $request->post();

        /**
         * =================================
         * 1. Get Available Fields
         * =================================
         */
        $allowedFields = User::getAllowedFields('player', 'c');

        /**
         * =================================
         * 2. Request Validation
         * =================================
         */
        $error = (new \App\Validators\Model\User)->validate($payload, $allowedFields, 'c');
        if (! empty($error)) {
            return Utils::responseError($error);
        }

        /**
         * =================================
         * 3. Create
         * =================================
         */
        $data             = Arr::only($payload, $allowedFields);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        if ($user) {
            $user->roles()->sync([
                Role::where('name', 'player')->value('id'),
            ]);
            Auth::login($user);
            return Utils::responseData();
        } else {
            return response('Internal Server Error', 500);
        }
    }

    public function sendPasswordResetLink(Request $request)
    {
        $host = $request->getHost();

        $mapRolesToHost = [
            env('APP_ADMIN_HOST', 'admin.tapsndr.com')   => ['master_admin', 'distributor'],
            env('APP_REDEEM_HOST', 'redeem.tapsndr.com') => ['player'],
        ];

        if (! isset($mapRolesToHost[$host])) {
            return response('Not Found', 404);
        }

        $payload = $request->post();

        $validator = Validator::make($payload, [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        $user = User::where('email', $payload['email'])
            ->whereHas('roles', function ($query) use ($mapRolesToHost, $host) {
                $query->whereIn('name', $mapRolesToHost[$host]);
            })
            ->first();
        if (! $user) {
            return Utils::responseError('User Not Found');
        }

        $status = Password::sendResetLink([
            'email' => $payload['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return Utils::responseError('Could not send the password reset link');
        }

        return Utils::responseData();
    }

    public function resetPassword(Request $request)
    {
        $payload = $request->post();

        $validator = Validator::make($payload, [
            'email'    => 'required|email',
            'token'    => 'required',
            // 'password' => 'required|min:6',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        $status = Password::reset(
            Arr::only($payload, ['email', 'token', 'password']),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return Utils::responseError('Could not reset the password');
        }

        return Utils::responseData();
    }

    public function signOut(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Utils::responseData();
    }
}
