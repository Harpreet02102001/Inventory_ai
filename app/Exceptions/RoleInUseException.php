<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * RoleInUseException
 *
 * Thrown when attempting to delete a role that is still assigned to
 * one or more users. An expected business-rule violation, not a
 * system failure.
 */
class RoleInUseException extends Exception
{
    //
}
