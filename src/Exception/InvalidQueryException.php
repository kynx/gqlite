<?php

declare(strict_types=1);

namespace Kynx\GqLite\Exception;

use PDOStatement;
use RuntimeException;
use Throwable;

use function assert;
use function is_string;
use function sprintf;

final class InvalidQueryException extends RuntimeException implements ExceptionInterface
{
    public static function fromPdoException(PDOStatement $statement, ?Throwable $previous): self
    {
        $message = $statement->errorInfo()[2];
        assert(is_string($message));

        return new self(sprintf(
            "Error executing statement: %s (%s %s)",
            $statement->queryString,
            (string) $statement->errorCode(),
            $message
        ), 0, $previous);
    }
}
