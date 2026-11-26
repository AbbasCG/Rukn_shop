<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Not enough stock to (re)take an order's quantities. Its message is meant
 * for the admin; any other exception (e.g. a database error) is not.
 */
class InsufficientStockException extends RuntimeException
{
}
