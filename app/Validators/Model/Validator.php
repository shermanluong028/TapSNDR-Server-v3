<?php
namespace App\Validators\Model;

abstract class Validator
{
    private $model = null;

    protected function __construct($model)
    {
        $this->model = $model;
    }

    public function validate($data, $allowedFields, $op): mixed
    {
        $modelName = strtolower(class_basename($this->model));

        if ($op === 'c' && $error = $this->validateEmpty($data, $allowedFields)) {
            return $error;
        }

        if ($op === 'u' && ! isset($data['id'])) {
            return trans('models/' . $modelName . '.empty_id');
        }

        if (isset($data['id'])) {
            $row = $this->model::find($data['id']);
            if (! $row) {
                return trans('models/' . $modelName . '.invalid_id');
            }

        }

        if ($error = $this->validateFields($data, $allowedFields, $op)) {
            return $error;
        }

        return null;
    }

    abstract protected function validateEmpty($data, $allowedFields): mixed;
    abstract protected function validateFields($data, $allowedFields, $op): mixed;
}
