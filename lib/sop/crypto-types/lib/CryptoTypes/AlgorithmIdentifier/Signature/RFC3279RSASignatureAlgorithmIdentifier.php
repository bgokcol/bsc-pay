<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Signature;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Primitive\NullType;

/* @formatter:off *//*

From RFC 3279 - 2.2.1  RSA Signature Algorithm:

   When any of these three OIDs appears within the ASN.1 type
   AlgorithmIdentifier, the parameters component of that type SHALL be
   the ASN.1 type NULL.

*//* @formatter:on */

/**
 * Base class for RSA signature algorithms specified in RFC 3279.
 *
 * @link https://tools.ietf.org/html/rfc3279#section-2.2.1
 */
abstract class RFC3279RSASignatureAlgorithmIdentifier extends RSASignatureAlgorithmIdentifier
{
    /**
     *
     * @param UnspecifiedType $params
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        if (!isset($params)) {
            throw new \UnexpectedValueException("No parameters.");
        }
        $params->asNull();
        return new static();
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return NullType
     */
    protected function _paramsASN1()
    {
        return new NullType();
    }
}
