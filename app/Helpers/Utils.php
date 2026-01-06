<?php
namespace App\Helpers;

use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class Utils
{
    //  TODO: In principle, status must be received as a parameter. But it is unnecessary because 200 response are sent for every request
    //  public static function responseData($status, $data = null) {
    //      ... ... ...
    //      return response()->status($status)->json($jsonData);
    //  }
    public static function responseData($data = null): JsonResponse
    {
        $jsonData = [
            'status' => 1,
        ];
        if (isset($data)) {
            $jsonData['data'] = $data;
        }
        return response()->json($jsonData);
    }

    public static function responseError($error = null): JsonResponse | HttpFoundationResponse
    {
        if ($error instanceof HttpFoundationResponse) {
            return $error;
        }
        $jsonError = [
            'status' => 0,
        ];
        if (isset($error)) {
            $jsonError['error'] = $error;
        }
        return response()->json($jsonError);
    }

    public static function getAllowedFields($mapRolesToFields, $role, $op): array
    {
        $allowedFields = [];
        if (
            ! is_array($mapRolesToFields)
        ) {
            return $allowedFields;
        }
        foreach ($mapRolesToFields as $field => $mapRolesToOp) {
            if (
                is_array($mapRolesToOp) &&
                isset($mapRolesToOp[$op]) &&
                is_array($mapRolesToOp[$op]) &&
                (
                    in_array('*', $mapRolesToOp[$op]) ||
                    in_array($role, $mapRolesToOp[$op])
                )
            ) {
                $allowedFields[] = $field;
            }
        }
        return $allowedFields;
    }

    public static function setConditions2Query(&$query, $conditions, $combineMode = 'and'): void
    {
//        if (is_bool($conditions)) {
//            $query->where($conditions);
//            return;
//        }
        if (is_array($conditions)) {
//            if (array_values($conditions) !== $conditions) {
//                $query->where($conditions);
//                return;
//            }
            $combineFunc = $combineMode === 'OR' ? 'orWhere' : 'where';
            foreach ($conditions as $condition) {
                if (is_bool($condition)) {
                    $query->{$combineFunc}(function ($query) use ($condition) {
                        $query->whereRaw($condition ? 'true' : 'false');
                    });
                }
                if (is_array($condition)) {
                    if (array_values($conditions) !== $conditions) {
                        $query->{$combineFunc}(function ($query) use ($conditions, $condition) {
                            $query->where($conditions);
                        });
                    } else if (count($condition) > 3) {
//                        array_pop($condition);
                        $query->{$combineFunc}(function ($query) use ($condition) {
                            self::setConditions2Query($query, $condition, end($condition));
                        });
                    } else {
                        $field    = null;
                        $operator = null;
                        $value    = null;

                        $field = $condition[0];
                        if (count($condition) === 3) {
                            $operator = $condition[1];
                            $value    = $condition[2];
                        } else {
                            $value = $condition[1];
                        }
                        if (is_array($field)) {
                            // For example, [['username', 'fullname'], 'jonatps']. This is typically used when searching.
                            $query->{$combineFunc}(function ($query) use ($value, $field, $operator) {
                                $conditions = self::makeConditions($field, $operator, $value);
                                // TODO: When $field is an array, I think it is very rare for the fields in it to be combined with "and".
                                // So, I assumed that they were always combined with "OR" and implemented it that way.
                                self::setConditions2Query($query, $conditions, 'OR');
                            });
                        }
                        if (is_array($value)) {
                            $query->{$combineFunc}(function ($query) use ($value, $field, $operator) {
                                if ($operator === 'NOT') {
                                    $query->whereNotIn($field, $value);
                                } else {
                                    $query->whereIn($field, $value);
                                }
                            });
                        } else if (is_callable($value)) {
                            $query->{$combineFunc}(function ($query) use ($value, $field) {
                                $query->whereHas($field, $value);
                            });
                        } else if (! is_null($operator)) {
                            // TODO: Need to test when $operator is null, I remember $operator seems to be considered as $value when that is null
                            $query->{$combineFunc}($field, $operator, $value);
                        } else {
                            $query->{$combineFunc}($field, $value);
                        }
                    }
                }
            }
        }
    }

    // This functions is used to make conditions with an array as field, for example, [[name, email], 'jonatps']
    public static function makeConditions($field, $operator, $value): array
    {
        $conditions = [];

        if (! is_array($field)) {
            return $conditions;
        }

        foreach ($field as $item) {
            $conditions[] = [$item, $operator, $value];
        }

        return $conditions;
    }

    public static function getTZOffset($timezone)
    {
        try {
            $tzOffsetSeconds = (new DateTime('now', new DateTimeZone($timezone)))->getOffset();
            $tzOffsetHours   = floor($tzOffsetSeconds / 3600);
            $tzOffsetMinutes = ($tzOffsetSeconds % 3600) / 60;
            $tzOffsetSign    = $tzOffsetSeconds >= 0 ? '+' : '-';
            return $tzOffsetSign . str_pad(abs($tzOffsetHours), 2, '0', STR_PAD_LEFT) . ':' . str_pad(abs($tzOffsetMinutes), 2, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return null;
        }
    }
}
