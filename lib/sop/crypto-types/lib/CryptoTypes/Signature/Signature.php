<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Signature;

use ASN1\Type\Primitive\BitString;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AlgorithmIdentifierType;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\ECSignatureAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\RSASignatureAlgorithmIdentifier;

/**
 * Base class for signature values.
 */
abstract class Signature
{
    /**
     * Get the signature as a BitString.
     *
     * @return BitString
     */
    abstract public function bitString(): BitString;
    
    /**
     * Get signature object by signature data and used algorithm.
     *
     * @param string $data Signature value
     * @param AlgorithmIdentifierType $algo Algorithm identifier
     * @return self
     */
    public static function fromSignatureData(string $data,
        AlgorithmIdentifierType $algo): Signature
    {
        if ($algo instanceof RSASignatureAlgorithmIdentifier) {
            return RSASignature::fromSignatureString($data);
        }
        if ($algo instanceof ECSignatureAlgorithmIdentifier) {
            return ECSignature::fromDER($data);
        }
        return new GenericSignature(new BitString($data), $algo);
    }
}
