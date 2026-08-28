<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

trait HandlesConstrainedDeletes
{
    protected function deleteUnlessReferenced(Model $model): bool
    {
        try {
            $model->delete();

            return true;
        } catch (QueryException $exception) {
            if (! $this->isForeignKeyConstraintViolation($exception)) {
                throw $exception;
            }

            return false;
        }
    }

    protected function isForeignKeyConstraintViolation( QueryException $exception ): bool {
        $sqlState = (string) (
            $exception->errorInfo[0] ?? $exception->getCode()
        );

        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        $driverMessage = strtoupper(
            (string) ($exception->errorInfo[2] ?? '')
        );

        // PostgreSQL
        if ($sqlState === '23503') {
            return true;
        }

        if (! in_array($sqlState, ['23000', '23001'], true)) {
            return false;
        }

        // MySQL/MariaDB, SQL Server และ SQLite
        return in_array($driverCode, [547, 1451, 1452], true)
            || (
                $driverCode === 19
                && str_contains($driverMessage, 'FOREIGN KEY')
            );
    }
}