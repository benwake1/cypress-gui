<?php

namespace App\Policies;

use App\Models\TestRun;
use App\Models\User;

class TestRunPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, TestRun $testRun): bool
    {
        return $user->isAdmin() || $user->id === $testRun->triggered_by;
    }
}
