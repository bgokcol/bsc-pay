<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Cipher;

/**
 * Base class for block cipher algorithm identifiers.
 */
abstract class BlockCipherAlgorithmIdentifier extends CipherAlgorithmIdentifier
{
    /**
     * Get block size in bytes.
     *
     * @return int
     */
    abstract public function blockSize(): int;
}
