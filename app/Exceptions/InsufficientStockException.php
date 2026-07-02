<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use Exception;

/**
 * InsufficientStockException
 *
 * Thrown when a stock reduction or sale confirmation is attempted
 * but the product does not have sufficient quantity available.
 *
 * This is a domain exception — it represents a business rule violation,
 * not a system or infrastructure error. It should produce a user-friendly
 * validation-style message, not a 500 server error page.
 *
 * Registered in bootstrap/app.php to render as:
 *   - 422 JSON response for API/AJAX requests
 *   - Redirect back with error flash for web requests
 */
class InsufficientStockException extends Exception
{
    /**
     * Create an InsufficientStockException.
     *
     * Builds a technical message for logging and a user-friendly message
     * for display, keeping both accessible from the same exception instance.
     *
     * @param  Product  $product    The product with insufficient stock
     * @param  int      $requested  The quantity that was requested to be deducted
     * @param  int      $available  The quantity currently available in stock
     */
    public function __construct(
        private readonly Product $product,
        private readonly int $requested,
        private readonly int $available,
    ) {
        parent::__construct(
            "Insufficient stock for [{$product->name}] (ID: {$product->id}). "
                . "Requested: {$requested}, Available: {$available}."
        );
    }

    /**
     * Get the product that triggered this exception.
     *
     * Useful for callers that need to reference the product
     * after catching this exception (e.g., to highlight it in a list).
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * Get a user-friendly error message for UI display.
     *
     * Written in plain language suitable for showing directly
     * to the end user in an error flash or form validation message.
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return "Not enough stock for \"{$this->product->name}\". "
            . "You requested {$this->requested} unit(s) but only "
            . "{$this->available} available.";
    }
}
