<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier;

use ASN1\Type\UnspecifiedType;

/**
 * Base class for algorithm identifiers implementing specific functionality and
 * parameter handling.
 */
abstract class SpecificAlgorithmIdentifier extends AlgorithmIdentifier
{
    /**
     * Initialize object from algorithm identifier parameters.
     *
     * @param UnspecifiedType|null $params Parameters or null if none
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        throw new \BadMethodCallException(
            __FUNCTION__ . " must be implemented in derived class.");
    }
}
