<?php

namespace Tests\Unit;

use App\Http\Controllers\Concerns\HandlesConstrainedDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Mockery;
use PDOException;
use PHPUnit\Framework\TestCase;

class HandlesConstrainedDeletesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_non_foreign_key_query_exception_is_rethrown(): void
    {
        $previous = new PDOException('Unique constraint failed');
        $previous->errorInfo = [
            '23000',
            19,
            'UNIQUE constraint failed: users.email',
        ];

        $exception = new QueryException(
            'sqlite',
            'delete from users',
            [],
            $previous
        );

        $model = Mockery::mock(Model::class);

        $model->shouldReceive('delete')
            ->once()
            ->andThrow($exception);

        $this->expectExceptionObject($exception);

        (new ConstrainedDeleteHarness())->delete($model);
    }
}

class ConstrainedDeleteHarness
{
    use HandlesConstrainedDeletes;

    public function delete(Model $model): bool
    {
        return $this->deleteUnlessReferenced($model);
    }
}