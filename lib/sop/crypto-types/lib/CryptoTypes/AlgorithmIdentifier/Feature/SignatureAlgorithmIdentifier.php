<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Feature;

use Sop\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;

/**
 * Algorithm identifier for signature algorithms.
 */
interface SignatureAlgorithmIdentifier extends AlgorithmIdentifierType
{
    /**
     * Check whether signature algorithm supports given key algorithm.
     *
     * @param AlgorithmIdentifier $algo
     * @return bool
     */
    public function supportsKeyAlgorithm(AlgorithmIdentifier $algo): bool;
}
