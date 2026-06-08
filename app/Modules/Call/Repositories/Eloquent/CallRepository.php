<?php

declare(strict_types=1);

namespace App\Modules\Call\Repositories\Eloquent;

use App\Modules\Call\Models\Call;
use App\Modules\Call\Repositories\Contracts\CallRepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CallRepository extends BaseRepository implements CallRepositoryInterface
{
    protected function model(): string
    {
        return Call::class;
    }

    public function history(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('caller_id', $userId)
            ->orWhere('callee_id', $userId)
            ->with(['caller.profile', 'callee.profile'])
            ->latest()
            ->paginate($perPage);
    }
}
