<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * InvalidSaleStatusException
 *
 * Thrown when a status transition is attempted that isn't valid from
 * the sale's current status — e.g. confirming an already-confirmed
 * sale, or cancelling one that's already confirmed. An expected
 * business-rule violation, not a system failure.
 */
class InvalidSaleStatusException extends Exception
{
    //
}
