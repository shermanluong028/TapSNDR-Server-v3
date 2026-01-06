<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\CryptoAddressRepository;
use App\Contracts\Repositories\DepositRepository;
use App\Helpers\Utils;
use App\Models\CryptoAddress;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    protected CryptoAddressRepository $cryptoAddressRepository;
    protected DepositRepository $depositRepository;

    public function __construct(
        CryptoAddressRepository $cryptoAddressRepository,
        DepositRepository $depositRepository
    ) {
        $this->cryptoAddressRepository = $cryptoAddressRepository;
        $this->depositRepository       = $depositRepository;
    }

    public function getAddress(Request $request)
    {
        $searchParams = $request->query();

        $validator = Validator::make($searchParams, [
            'address_from' => 'required|string|size:42',
            'amount'       => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        $currentUser = $request->user();

        DB::beginTransaction();

        $cryptoAddress = CryptoAddress::whereRaw('LOWER(address) = "' . strtolower($searchParams['address_from']) . '"')->first();
        if ($cryptoAddress) {
            if ($cryptoAddress->user_id !== $currentUser->id) {
                DB::commit();
                return Utils::responseError('This wallet address is already in use by another user.');
            }
        }
        $currentUser->crypto_addresses()->create([
            'address' => $searchParams['address_from'],
        ]);

        DB::commit();

        $depositAddress = Wallet::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            });
        })->value('address');

        return Utils::responseData($depositAddress);
    }

    public function deposit(Request $request)
    {
        $currentUser = $request->user();

        $payload = $request->post();

        $validator = Validator::make($payload, [
            'txhash' => 'required|string|size:66',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        $txhash = $payload['txhash'];

        sleep(5);

        list($transaction, $error) = $this->depositRepository->deposit($txhash, $currentUser);
        if ($error) {
            return Utils::responseError($error);
        }

        return Utils::responseData();
    }
}
