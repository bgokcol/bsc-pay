<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Signature;

use ASN1\Type\UnspecifiedType;
use Sop\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\SignatureAlgorithmIdentifier;

/* @formatter:off *//*

From RFC 5758 - 3.2.  ECDSA Signature Algorithm

   When the ecdsa-with-SHA224, ecdsa-with-SHA256, ecdsa-with-SHA384, or
   ecdsa-with-SHA512 algorithm identifier appears in the algorithm field
   as an AlgorithmIdentifier, the encoding MUST omit the parameters
   field.

*//* @formatter:on */

/**
 * Base class for ECDSA signature algorithm identifiers.
 *
 * @link https://tools.ietf.org/html/rfc5758#section-3.2
 * @link https://tools.ietf.org/html/rfc5480#appendix-A
 */
abstract class ECSignatureAlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    SignatureAlgorithmIdentifier
{
    /**
     *
     * @param UnspecifiedType $params
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        if (isset($params)) {
            throw new \UnexpectedValueException("Parameters must be omitted.");
        }
        return new static();
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return null
     */
    protected function _paramsASN1()
    {
        return null;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function supportsKeyAlgorithm(AlgorithmIdentifier $algo): bool
    {
        return $algo->oid() == self::OID_EC_PUBLIC_KEY;
    }
}
