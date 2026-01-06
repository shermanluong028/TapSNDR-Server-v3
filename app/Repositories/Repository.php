<?php
namespace App\Repositories;

use App\Helpers\Utils;

class Repository
{
    protected $model     = null;
    protected $validator = null;

    public function __construct($model, $validator)
    {
        $this->model     = $model;
        $this->validator = $validator;
    }

    public function upsert($data, $currentUser)
    {
        $id = $data['id'] ?? null;

        $op = $id ? 'u' : 'c';

        $allowedFields = $this->model::getAllowedFields($currentUser->role, $op);

        if (isset($this->validator)) {
            $error = (new ($this->validator)())->validate($data, $allowedFields, $op);
            if ($error) {
                return response()->json([
                    'status' => 0,
                    'error'  => $error,
                ]);
            }
        }

        if ($op === 'u') {
            if (! $this->checkUpdatableById($id, $currentUser)) {
                return response('Forbidden', 403);
            }
        }

        $data = array_intersect_key($data, array_flip($allowedFields));

        if ($op === 'c') {
            $this->fillData($data, $op);
        }

        $originalRecord = $this->model::find($id);

        $updatedRecord = $this->model::updateOrCreate(['id' => $id], $data);

        $error = $this->afterUpsert(
            $data,
            $originalRecord,
            $updatedRecord
        );
        if ($error) {
            return [$error];
        }

        if (! $this->checkCreatable($updatedRecord, $currentUser)) {
            return response('Forbidden', 403);
        }

        return [
            null, [$originalRecord, $updatedRecord],
        ];
    }

    private function checkReadableById($id, $user): bool
    {
        $conditions   = $this->model::getConditionsForReadableRecords($user);
        $conditions[] = is_array($id) ? $id : ['id', $id];
        $row          = $this->model::where(function ($subquery) use ($conditions) {
            Utils::setConditions2Query($subquery, $conditions);
        })->first();
        return ! ! $row;
    }

    protected function checkCreatable($row, $user): bool
    {
        return $this->checkReadableById($row->id, $user);
    }

    protected function checkUpdatableById($id, $user): bool
    {
        return $this->checkReadableById($id, $user);
    }

    protected function fillData(&$data, $op): void
    {
        return;
    }

    protected function afterUpsert(
        $data,
        $originalRecord,
        $updatedRecord
    ): mixed {
        return null;
    }
}
