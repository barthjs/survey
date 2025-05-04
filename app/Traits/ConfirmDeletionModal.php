<?php

declare(strict_types=1);

namespace App\Traits;

trait ConfirmDeletionModal
{
    public bool $confirmDeletionModal = false;

    public ?string $deletionId = null;

    public function confirmDeletion(?string $id = null): void
    {
        $this->deletionId = $id;
        $this->confirmDeletionModal = true;
    }

    public function closeConfirmDeletionModal(): void
    {
        $this->confirmDeletionModal = false;
        $this->deletionId = null;
    }
}
