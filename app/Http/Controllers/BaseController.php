<?php
namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    private mixed $model          = null;
    private mixed $validator      = null;
    protected mixed $repositories = [];

    public function __construct($model, $validator = null, $options = [])
    {
        $this->model     = $model;
        $this->validator = $validator;
        $fileOptions     = [
            'files' => ! empty($options['files']) ? $options['files'] : [],
        ];
        $this->middleware('upload:' . serialize($fileOptions))->only('post');
    }

    public function get(Request $request, ...$params): JsonResponse
    {
        $currentUser = $request->user();
        $apiKey      = $request->apiKey();

        /**
         * =================================
         * 1. Get Available Fields
         * =================================
         */
        $allowedFields = $this->model::getAllowedFields(
            $currentUser->role ?? ($apiKey ? 'api' : null) ?? 'guest',
            'r'
        );

        /**
         * =================================
         * 2. Request Validation
         * =================================
         */
        // Relationships
        $relationshipsForStructure = $request->query('with');

        // Search Option
        $searchField = $request->query('searchField');
        $searchValue = $request->query('searchValue');

        // Order Option
        $orderField     = $request->query('orderField');
        $orderDirection = $request->query('orderDirection');

        // Pagination Option
        $pageLength = intval($request->query('pageLength'));
        $pageIndex  = intval($request->query('pageIndex'));

        $conditions = $this->model::getConditionsForReadableRecords($currentUser);
        $conditions = array_merge($conditions, $this->getAdditionalConditions($request));

        $orderOptions = [[$orderField, $orderDirection]];
        $orderOptions = array_merge($this->getAdditionalOrderOptions($request), $orderOptions);

        /**
         * =================================
         * 3. Get
         * =================================
         */
        list($total, $data) = $this->getData(
            model: $this->model,
            allowedFields: $allowedFields,
            conditions: $conditions,
            searchField: $searchField,
            searchValue: $searchValue,
            orderOptions: $orderOptions,
            pageLength: $pageLength,
            pageIndex: $pageIndex,
            relationshipsForStructure: $relationshipsForStructure,
        );

        $data = $data->toArray();

        $error = $this->afterGet($request, $data);
        if ($error) {
            return Utils::responseError($error);
        }

        return Utils::responseData(is_null($total) ? $data : [
            'data'  => $data,
            'total' => $total,
        ]);
    }

    public function getById(Request $request, $id): JsonResponse | ResponseFactory
    {
        $currentUser = $request->user();
        /**
         * =================================
         * 1. Request Validation
         * =================================
         */
        // $error = $this->validateId($id);
        // if ($error) {
        //     return Utils::responseError($error);
        // }

        if (! $this->checkReadableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        /**
         * =================================
         * 2. Get Available Fields
         * =================================
         */
        $allowedFields = $this->model::getAllowedFields(Auth::user()->role ?? 'guest', 'r');

        $relationshipsForStructure = $request->query('with');

        $error = $this->beforeGetById($id);
        if ($error) {
            return Utils::responseError($error);
        }

        /**
         * =================================
         * 3. Get User
         * =================================
         */
        $data = $this->model::select($allowedFields)
            ->with($this->getRelationshipsForData($this->getRelationshipsStructure($relationshipsForStructure)))
            ->where(is_array($id) ? $id : ['id' => $id])
            ->first();
        if (! $data) {
            return Utils::responseError(trans('invalid_id'));
        }

        $data = $data->toArray();

        $error = $this->afterGetById($request, $data);
        if ($error) {
            return Utils::responseError($error);
        }

        return Utils::responseData($data);
    }

    public function getRelatedDataById(Request $request, $id, $model, $foreignKey, $hasOne = false): JsonResponse | ResponseFactory
    {
        $currentUser = $request->user();
        /**
         * =================================
         * 1. Request Validation
         * =================================
         */
        // $error = $this->validateId($id);
        // if ($error) {
        //     return Utils::responseError($error);
        // }

        if (! $this->checkReadableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        /**
         * =================================
         * 2. Get Available Fields
         * =================================
         */
        $allowedFields = $model::getAllowedFields(Auth::user()->role ?? 'guest', 'r');

        // Relationships
        $relationshipsForStructure = $request->query('with');

        // if (! $hasOne) {
        // Search Option
        $searchField = $request->query('searchField');
        $searchValue = $request->query('searchValue');

        // Order Option
        $orderField     = $request->query('orderField');
        $orderDirection = $request->query('orderDirection');

        // Pagination Option
        $pageLength = intval($request->query('pageLength'));
        $pageIndex  = intval($request->query('pageIndex'));
        // }

        $conditions   = $model::getConditionsForReadableRecords(Auth::user());
        $conditions   = array_merge($conditions, $this->getAdditionalConditionsForRelatedData($request, $id, $model));
        $conditions[] = [$foreignKey, $id];

        $orderOptions = [[$orderField, $orderDirection]];
        $orderOptions = array_merge($this->getAdditionalOrderOptionsForRelatedData($request, $id, $model), $orderOptions);

        /**
         * =================================
         * 3. Get
         * =================================
         */
        list($total, $data) = $this->getData(
            model: $model,
            allowedFields: $allowedFields,
            conditions: $conditions,
            searchField: $searchField,
            searchValue: $searchValue,
            orderOptions: $orderOptions,
            pageLength: $pageLength,
            pageIndex: $pageIndex,
            relationshipsForStructure: $relationshipsForStructure,
        );
        if ($hasOne) {
            $data = $data->first();
        }

        $error = $this->afterGetRelatedDataById($data, $model);
        if ($error) {
            return Utils::responseError($error);
        }

        return Utils::responseData((is_null($total) || $hasOne) ? $data : [
            'data'  => $data,
            'total' => $total,
        ]);
    }

    public function getRelatedDataById_v2(
        Request $request,
        $id,
        $relationName,
        $foreignRelationName,
        $hasOne = false
    ): JsonResponse | ResponseFactory {
        $currentUser = $request->user();

        /**
         * =================================
         * 1. Request Validation
         * =================================
         */
        // $error = $this->validateId($id);
        // if ($error) {
        //     return Utils::responseError($error);
        // }

        if (! $this->checkReadableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        $relatedModel = $this->model->{$relationName}()->getRelated();

        /**
         * =================================
         * 2. Get Available Fields
         * =================================
         */
        $allowedFields = $relatedModel::getAllowedFields(Auth::user()->role ?? 'guest', 'r');

        // Relationships
        $relationshipsForStructure = $request->query('with');

        // if (! $hasOne) {
        // Search Option
        $searchField = $request->query('searchField');
        $searchValue = $request->query('searchValue');

        // Order Option
        $orderField     = $request->query('orderField');
        $orderDirection = $request->query('orderDirection');

        // Pagination Option
        $pageLength = intval($request->query('pageLength'));
        $pageIndex  = intval($request->query('pageIndex'));
        // }

        $conditions = [
            [
                $foreignRelationName,
                function ($query) use ($id, $relatedModel) {
                    $query->where($this->model->getTable() . '.id', $id);
                },
            ],
        ];
        $conditions = array_merge($conditions, $relatedModel::getConditionsForReadableRecords(Auth::user()));
        $conditions = array_merge($conditions, $this->getAdditionalConditionsForRelatedData($request, $id, $relatedModel));

        $orderOptions = [[$orderField, $orderDirection]];
        $orderOptions = array_merge($this->getAdditionalOrderOptionsForRelatedData($request, $id, $relatedModel), $orderOptions);

        /**
         * =================================
         * 3. Get
         * =================================
         */
        list($total, $data) = $this->getData(
            model: $relatedModel,
            allowedFields: $allowedFields,
            conditions: $conditions,
            searchField: $searchField,
            searchValue: $searchValue,
            orderOptions: $orderOptions,
            pageLength: $pageLength,
            pageIndex: $pageIndex,
            relationshipsForStructure: $relationshipsForStructure,
        );

        $data = $data->toArray();

        $error = $this->afterGetRelatedDataById_v2($request, $data, $relationName);
        if ($error) {
            return Utils::responseError($error);
        }

        if ($hasOne) {
            $data = $data[0];
        }

        return Utils::responseData((is_null($total) || $hasOne) ? $data : [
            'data'  => $data,
            'total' => $total,
        ]);
    }

    public function post(Request $request): JsonResponse | HttpFoundationResponse
    {
        $currentUser = $request->user();
        $apiKey      = $request->apiKey();

        $payload = $request->post();

        if (! isset($payload['id'])) {
            // Create
            /**
             * =================================
             * 1. Get Available Fields
             * =================================
             */
            $allowedFields = $this->model::getAllowedFields(
                $currentUser->role ?? ($apiKey ? 'api' : null) ?? 'guest',
                'c'
            );

            /**
             * =================================
             * 2. Request Validation
             * =================================
             */
            if (isset($this->validator)) {
                $error = $this->validator->validate($payload, $allowedFields, 'c');
                if ($error) {
                    return Utils::responseError($error);
                }
            }

            /**
             * =================================
             * 3. Create
             * =================================
             */
            $data = Arr::only($payload, $allowedFields);
            $this->fillData(
                $request,
                $data,
                'c'
            );

            DB::beginTransaction();

            $record = $this->model::create($data);
            if (! $record) {
                DB::rollBack();
                return response('Internal Server Error', 500);
            }

            $error = $this->afterUpsert(
                $request,
                $data,
                null,
                $record
            );
            if ($error) {
                DB::rollBack();
                if ($error instanceof HttpFoundationResponse) {
                    return $error;
                } else {
                    return Utils::responseError($error);
                }
            }

            if (! $this->checkCreatable($currentUser ?: $apiKey->user, $record, 'c')) {
                DB::rollBack();
                return response('Forbidden', 403);
            }

            $error = $this->completeUpsert(
                $request,
                null,
                $record
            );
            if ($error) {
                DB::rollBack();
                if ($error instanceof HttpFoundationResponse) {
                    return $error;
                } else {
                    return Utils::responseError($error);
                }
            }

            DB::commit();

            return Utils::responseData(['id' => $record->id]);
        } else {
            // Update
            /**
             * =================================
             * 1. Get Available Fields
             * =================================
             */
            $allowedFields = $this->model::getAllowedFields(Auth::user()->role ?? 'guest', 'u');

            /**
             * =================================
             * 2. Request Validation
             * =================================
             */
            if (isset($this->validator)) {
                $error = $this->validator->validate($payload, $allowedFields, 'u');
                if ($error) {
                    return Utils::responseError($error);
                }
            }

            $id = $payload['id'];

            if (! $this->checkUpdatableById($currentUser, $id)) {
                return response('Forbidden', 403);
            }

            /**
             * =================================
             * 3. Update
             * =================================
             */
            $data = Arr::only($payload, $allowedFields);
            $this->fillData(
                $request,
                $data,
                'u'
            );

            DB::beginTransaction();

            $originalRecord = $this->model::find($id);

            $result = $this->model::find($id)->update($data);
            if (! $result) {
                DB::rollBack();
                return response('Internal Server Error', 500);
            }

            $updatedRecord = $originalRecord->fresh();

            $error = $this->afterUpsert(
                $request,
                $data,
                $originalRecord,
                $updatedRecord
            );
            if ($error) {
                DB::rollBack();
                if ($error instanceof HttpFoundationResponse) {
                    return $error;
                } else {
                    return Utils::responseError($error);
                }
            }

            if (! $this->checkCreatable($currentUser, $updatedRecord, 'u')) {
                DB::rollBack();
                return response('Forbidden', 403);
            }

            $error = $this->completeUpsert(
                $request,
                $originalRecord,
                $updatedRecord
            );
            if ($error) {
                DB::rollBack();
                if ($error instanceof HttpFoundationResponse) {
                    return $error;
                } else {
                    return Utils::responseError($error);
                }
            }

            DB::commit();

            return Utils::responseData();
        }
    }

    public function delete(Request $request): JsonResponse | HttpFoundationResponse
    {
        $currentUser = $request->user();

        /**
         * =================================
         * 1. Request Validation
         * =================================
         */
        $id = $request->all()['id'] ?? null;

        // $error = $this->validateId($id);
        // if ($error) {
        //     return Utils::responseError($error);
        // }

        if (! $this->checkIfRowCanBeDeletedById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        $error = $this->beforeDelete($id);
        if ($error) {
            return Utils::responseError($error);
        }

        /**
         * =================================
         * 2. Delete row with id
         * =================================
         */
        $deleted = $this->model::destroy($id);
        if ($deleted) {
            return Utils::responseData();
        } else {
            return Utils::responseError(trans('invalid_id'));
        }
    }

    protected function validateId($id): ?string
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        if (! $this->model::find($id)) {
            return Utils::responseError(trans('invalid_id'));
        }
        return null;
    }

    private function validateSearchOptions($searchField, $allowedFields): bool
    {
        $searchFieldType = gettype($searchField);
        if ($searchFieldType !== 'string' && $searchFieldType !== 'array') {
            return false;
        }
        if ($searchFieldType === 'string' && ! in_array($searchField, $allowedFields)) {
            return false;
        }
        if ($searchFieldType === 'array') {
            for ($i = 0; $i < count($searchField); $i++) {
                if (! in_array($searchField[$i], $allowedFields)) {
                    array_splice($searchField, $i, 1);
                    $i--;
                }
            }
            if (count($searchField) === 0) {
                return false;
            }

        }
        return true;
    }

    private function validateOrderOption($orderField, $orderDirection, $allowedFields): bool
    {
        if (gettype($orderField) !== 'string' && gettype($orderField) !== 'object') {
            return false;
        }

        if (gettype($orderField) === 'string' && ! in_array($orderField, $allowedFields)) {
            return false;
        }

        if ($orderDirection !== 'desc' && $orderDirection !== 'asc') {
            return false;
        }

        return true;
    }

    private function validatePaginationOptions($pageLength, $pageIndex): bool
    {
        $validator = Validator::make(['pageLength' => $pageLength, 'pageIndex' => $pageIndex], [
            'pageLength' => 'integer|min:5|max:1000',
            'pageIndex'  => 'integer|min:0',
        ]);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }

    final protected function getRelationshipsStructure($relationshipsForStructure): ?array
    {
        if (! is_array($relationshipsForStructure)) {
            return [];
        }
        $relationshipsStructure = [];
        for ($i = 0; $i < count($relationshipsForStructure); $i++) {
            if (! is_string($relationshipsForStructure[$i])) {
                continue;
            }
            $relationships = explode('.', $relationshipsForStructure[$i]);
            for ($j = 0; $j < count($relationships); $j++) {
                $currentDeep = &$relationshipsStructure;
                for ($k = 0; $k < $j; $k++) {
                    $currentDeep = &$currentDeep[$relationships[$k]];
                }
                if (! isset($currentDeep[$relationships[$j]])) {
                    $currentDeep[$relationships[$j]] = [];
                }
            }
        }
        return $relationshipsStructure;
    }

    final protected function getRelationshipsForData($relationshipsStructure, $model = null): array
    {
        $relationshipsForData = [];
        $relationships        = array_keys($relationshipsStructure);
        foreach ($relationships as $relationship) {
            if (method_exists($model ?? $this->model, $relationship)) {
                $relatedModel                        = ($model ?? $this->model)->{$relationship}()->getRelated();
                $relationshipsForData[$relationship] = function ($subquery) use ($relationshipsStructure, $relationship, $relatedModel) {
                    $allowedFields = $relatedModel::getAllowedFields(Auth::user()->role ?? 'guest', 'r');
                    $subquery->select(array_map(function ($field) use ($relatedModel) {
                        return $relatedModel->getTable() . '.' . $field;
                    }, $allowedFields))
                        ->with($this->getRelationshipsForData($relationshipsStructure[$relationship], $relatedModel))
                        ->where(function ($subquery) use ($relatedModel) {
                            Utils::setConditions2Query($subquery, $relatedModel::getConditionsForReadableRecords(Auth::user()));
                        });
                };
            }
        }
        return $relationshipsForData;
    }

    private function getData($model, $allowedFields, $conditions, $searchField, $searchValue, $orderOptions, $pageLength, $pageIndex, $relationshipsForStructure = []): array
    {
        $query = $model::select($allowedFields)
            ->with($this->getRelationshipsForData($this->getRelationshipsStructure($relationshipsForStructure), $model))
            ->where(function ($subquery) use ($conditions) {
                if ($conditions) {
                    Utils::setConditions2Query($subquery, $conditions);
                }
            })
            ->where(function ($subquery) use ($allowedFields, $searchValue, $searchField) {
                if ($this->validateSearchOptions($searchField, $allowedFields)) {
                    Utils::setConditions2Query($subquery, [[$searchField, 'like', '%' . $searchValue . '%']]);
                }
            });
        foreach ($orderOptions as $orderOption) {
            if (is_string($orderOption)) {
                $query->orderByRaw($orderOption);
            } else {
                [$orderField, $orderDirection] = $orderOption;
                if ($this->validateOrderOption($orderField, $orderDirection, $allowedFields)) {
                    $query->orderBy($orderField, $orderDirection);
                }
            }
        }
        $total = null;
        if ($this->validatePaginationOptions($pageLength, $pageIndex)) {
            $total = $query->count();
            $query->skip($pageLength * $pageIndex)->take($pageLength);
        }
        $data = $query->get();

        return [$total, $data];
    }

    protected function checkReadableById(?User $user, $id): bool
    {
        $conditions   = $this->model::getConditionsForReadableRecords($user);
        $conditions[] = is_array($id) ? $id : ['id', $id];
        $row          = $this->model::where(function ($subquery) use ($conditions) {
            Utils::setConditions2Query($subquery, $conditions);
        })->first();
        return ! ! $row;
    }

    protected function checkCreatable(?User $user, $record, $operation): bool
    {
        return $this->checkReadableById($user, $record->id);
    }

    protected function checkUpdatableById(?User $user, $id): bool
    {
        return $this->checkReadableById($user, $id);
    }

    protected function checkIfRowCanBeDeletedById(?User $user, $id): bool
    {
        return $this->checkReadableById($user, $id);
    }

    protected function getAdditionalConditions(Request $request): array
    {
        return [];
    }

    protected function getAdditionalOrderOptions(Request $request): array
    {
        return [];
    }

    protected function afterGet(Request $request, &$data)
    {
        return null;
    }

    protected function beforeGetById($id)
    {
        return null;
    }

    protected function afterGetById(Request $request, &$data)
    {
        return null;
    }

    protected function fillData(
        Request $request,
        &$data,
        $operation
    ): void {
    }

    protected function afterUpsert(
        Request $request,
        $data,
        $originalRecord,
        $updatedRecord
    ): mixed {
        return null;
    }

    protected function completeUpsert(
        Request $request,
        $originalRecord,
        $updatedRecord
    ): mixed {
        return null;
    }

    protected function beforeDelete($id)
    {
        return null;
    }

    protected function getAdditionalConditionsForRelatedData($request, $id, $model): array
    {
        return [];
    }

    protected function getAdditionalOrderOptionsForRelatedData($request, $id, $model): array
    {
        return [];
    }

    protected function afterGetRelatedDataById(&$data, $model)
    {
        return null;
    }

    protected function afterGetRelatedDataById_v2(Request $request, &$data, $relationName)
    {
        return null;
    }
}
