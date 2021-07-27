<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Hash;

use ASN1\Type\UnspecifiedType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\HashAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\PRFAlgorithmIdentifier;

/* @formatter:off *//*

Per RFC 2898 this algorithm identifier has no parameters:

algid-hmacWithSHA1 AlgorithmIdentifier {{PBKDF2-PRFs}} ::=
    {algorithm id-hmacWithSHA1, parameters NULL : NULL}

*//* @formatter:on */

/**
 * HMAC-SHA-1 algorithm identifier.
 *
 * @link http://www.alvestrand.no/objectid/1.2.840.113549.2.7.html
 * @link http://www.oid-info.com/get/1.2.840.113549.2.7
 * @link https://tools.ietf.org/html/rfc2898#appendix-C
 */
class HMACWithSHA1AlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    HashAlgorithmIdentifier,
    PRFAlgorithmIdentifier
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_oid = self::OID_HMAC_WITH_SHA1;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function name(): string
    {
        return "hmacWithSHA1";
    }
    
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
        return new self();
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
}
