<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * InvalidPurchaseOrderStatusException
 *
 * Thrown when a status transition is attempted that isn't allowed from
 * the PO's current status — e.g. trying to receive a PO that's already
 * 'received' or 'cancelled'. This is an expected business-rule violation
 * (often caused by double-clicks or stale page state), not a system
 * failure, so it's caught and shown as a precise message rather than
 * logged as an unexpected error.
 */
class InvalidPurchaseOrderStatusException extends Exception
{
    //
}
