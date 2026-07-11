<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * UserRepository
 *
 * Handles queries for the User model.
 */
class UserRepository extends BaseRepository
{
    /**
     * @param User $user Injected fresh by the service container
     */
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    /**
     * Paginated users, optionally filtered by name/email search, with
     * roles eager loaded to avoid N+1 when displaying role badges per row.
     *
     * @param int $perPage
     * @param string|null $search
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithSearch(
        int $perPage = 10,
        ?string $search = null,
    ): LengthAwarePaginator {
        return $this->model
            ->with('roles:id,name,display_name')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
