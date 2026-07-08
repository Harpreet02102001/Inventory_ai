<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * UserController
 *
 * Handles HTTP requests for User management. Includes a self-edit
 * guard: an Admin cannot edit their own account through this panel —
 * this prevents the single most common RBAC accident (an Admin
 * deactivating themselves, or removing their own last role, and
 * locking everyone — including themselves — out of the system).
 */
class UserController extends Controller
{
    /**
     * @param UserService $userService
     * @param UserRepository $users
     * @param RoleRepository $roles Populates the role checkbox list
     */
    public function __construct(
        private readonly UserService $userService,
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
    ) {}

    /**
     * Display a paginated, searchable list of users.
     *
     * @param Request $request May contain a 'search' query param
     * @return View
     */
    public function index(Request $request): View
    {
        return view('users.index', [
            'users'  => $this->users->getPaginatedWithSearch(15, $request->query('search')),
            'search' => $request->query('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        return view('users.create', [
            'roles' => $this->roles->getAllWithCounts(),
        ]);
    }

    /**
     * Persist a new user with their assigned roles.
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser(
                data: $request->safe()->only(['name', 'email', 'password', 'status']),
                roleIds: $request->validated('roles'),
            );
        } catch (Throwable $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the user. Please try again.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    /**
     * Show the pre-filled edit form.
     *
     * Refuses if the target is the currently logged-in user — see
     * class docblock for why self-editing is blocked entirely here.
     *
     * @param Request $request
     * @param User $user
     * @return View|RedirectResponse
     */
    public function edit(Request $request, User $user): View|RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot edit your own account from here.');
        }

        return view('users.edit', [
            'user'  => $user->load('roles:id,name,display_name'),
            'roles' => $this->roles->getAllWithCounts(),
        ]);
    }

    /**
     * Persist changes to a user and their role set.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot edit your own account from here.');
        }

        // Only include 'password' in the update data if it was actually
        // submitted — an empty string here would otherwise overwrite a
        // real password hash with a hash of an empty string.
        $data = $request->safe()->only(['name', 'email', 'status']);
        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        try {
            $this->userService->updateUser(
                userId: $user->id,
                data: $data,
                roleIds: $request->validated('roles'),
            );
        } catch (Throwable $e) {
            Log::error('User update failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the user. Please try again.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }
}
