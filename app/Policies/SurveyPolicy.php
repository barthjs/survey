<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;

final class SurveyPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Survey $survey): bool
    {
        return $survey->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Survey $survey): bool
    {
        return $survey->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Survey $survey): bool
    {
        return $survey->user_id === $user->id;
    }
}
