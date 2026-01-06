<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\CommissionPercentageRepository;
use App\Helpers\Utils;
use App\Models\FormDomain;
use App\Models\FormGameOption;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DomainsController extends BaseController
{
    public function __construct(
        CommissionPercentageRepository $commissionPercentageRepository
    ) {
        parent::__construct(
            model: new FormDomain,
            validator: new \App\Validators\Model\FormDomain,
        );
        $this->repositories['commission_percentage'] = $commissionPercentageRepository;
    }

    public function getByVendorCode(Request $request, $vendorCode): JsonResponse | ResponseFactory
    {
        $currentUser = $request->user();

        $allowedFields = FormDomain::getAllowedFields($currentUser?->role ?? 'guest', 'r');

        $relationshipsForStructure = $request->query('with');

        $domain = FormDomain::select($allowedFields)
            ->with($this->getRelationshipsForData($this->getRelationshipsStructure($relationshipsForStructure)))
            ->where('vendor_code', $vendorCode)
            ->first();
        if (! $domain) {
            return Utils::responseError('Invalid Vendor Code');
        }

        if (! $this->checkReadableById($currentUser, $domain->id)) {
            return response('Forbidden', 403);
        }

        $domain = $domain->toArray();

        $error = $this->afterGetById($request, $domain);
        if ($error) {
            return Utils::responseError($error);
        }

        return Utils::responseData($domain);
    }

    public function getGamesById(Request $request, $id)
    {
        return $this->getRelatedDataById($request, $id, FormGameOption::class, 'domain_id');
    }

    public function getCommissionPercentageById(Request $request, $id)
    {
        return $this->getRelatedDataById_v2($request, $id, 'commission_percentage', 'domains', true);
    }

    protected function getAdditionalConditions(Request $request): array
    {
        $searchParams = $request->query();

        $conditions = [];

        if (isset($searchParams['search_key'])) {
            $conditions[] = ['domain', 'LIKE', '%' . $searchParams['search_key'] . '%'];
        }

        return $conditions;
    }

    protected function afterGet(Request $request, &$data)
    {
        $currentUser = $request->user();

        if ($currentUser->role === 'player') {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['commission_percentage'] =
                    ($data[$i]['commission_percentage']['admin_customer'] ?? null ?: 4) +
                    ($data[$i]['commission_percentage']['distributor_customer'] ?? null ?: 0);
            }
        }

        return null;
    }

    protected function afterGetById(Request $request, &$data)
    {
        $currentUser = $request->user();

        if (! $currentUser || $currentUser->role === 'player') {
            $data['commission_percentage'] =
                ($data['commission_percentage']['admin_customer'] ?? null ?: 4) +
                ($data['commission_percentage']['distributor_customer'] ?? null ?: 0);
        }

        return null;
    }

    protected function fillData(Request $request, &$data, $op): void
    {
        if ($op === 'c') {
            $data['active']                = 1;
            $data['original_form_enabled'] = 0;
        }

        if (
            array_key_exists('group_name', $data) &&
            is_null($data['group_name'])
        ) {
            $data['group_name'] = '';
        }

        if (
            array_key_exists('telegram_chat_id', $data) &&
            is_null($data['telegram_chat_id'])
        ) {
            $data['telegram_chat_id'] = '';
        }
    }

    protected function afterUpsert(
        Request $request,
        $data,
        $originalRecord,
        $updatedRecord
    ): mixed {
        $currentUser = $request->user();

        if (array_key_exists('games', $data)) {
            $data['games'] = array_unique($data['games']);
            if ($originalRecord) {
                $updatedRecord->games()
                    ->where(function ($query) use ($data, $updatedRecord) {
                        $query
                            ->whereNotIn('game_name', $data['games'])
                            ->orWhere(function ($query) use ($updatedRecord) {
                                $query->whereNotIn(
                                    'id',
                                    $updatedRecord->games()
                                        ->selectRaw('MIN(id) as id')
                                        ->groupBy('game_name')
                                        ->pluck('id')
                                );
                            });
                    })
                    ->delete();
            }
            $games = $updatedRecord->games->toBase();
            for ($i = 0; $i < count($data['games']); $i++) {
                if ($originalRecord && $games->contains('game_name', $data['games'][$i])) {
                    continue;
                }
                $updatedRecord->games()->create([
                    'game_name'     => $data['games'][$i],
                    'display_order' => $updatedRecord->games()->max('display_order') + 1,
                    'active'        => 1,
                ]);
            }
        }

        if (array_key_exists('commission_percentage', $data)) {
            $commissionPercentageData              = $data['commission_percentage'];
            $commissionPercentageData['id']        = $updatedRecord->commission_percentage->id ?? null;
            $commissionPercentageData['domain_id'] = $updatedRecord->id;

            $result = $this->repositories['commission_percentage']->upsert($commissionPercentageData, $currentUser);
            if ($result instanceof HttpFoundationResponse) {
                return $result;
            } else {
                list($error) = $result;
                if ($error) {
                    return $error;
                }
            }
        }

        return null;
    }
}
