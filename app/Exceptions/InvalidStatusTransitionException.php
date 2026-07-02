<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * InvalidStatusTransitionException
 *
 * Thrown when a status change is attempted that violates the defined
 * workflow transitions for an entity (Purchase Order or Sale).
 *
 * Status workflows are like state machines:
 *   Purchase Order: draft → ordered → received (or cancelled at any point)
 *   Sale: draft → confirmed (or cancelled at any point)
 *
 * Invalid transitions:
 *   - Receiving a draft PO (must be 'ordered' first)
 *   - Confirming a cancelled sale
 *   - Cancelling an already received PO
 *
 * Registered in bootstrap/app.php for clean user-facing responses.
 */
class InvalidStatusTransitionException extends Exception
{
    /**
     * Create an InvalidStatusTransitionException.
     *
     * @param  string  $entity    Human-readable entity name e.g. "Purchase Order"
     * @param  string  $current   The entity's current status e.g. "draft"
     * @param  string  $attempted The status transition that was attempted e.g. "received"
     */
    public function __construct(
        private readonly string $entity,
        private readonly string $current,
        private readonly string $attempted,
    ) {
        parent::__construct(
            "Invalid status transition for [{$entity}]: "
                . "cannot move from [{$current}] to [{$attempted}]."
        );
    }

    /**
     * Get a user-friendly error message for UI display.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return "This {$this->entity} cannot be set to \"{$this->attempted}\" "
            . "because its current status is \"{$this->current}\".";
    }
}
