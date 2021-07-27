<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Asymmetric;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Primitive\NullType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AsymmetricCryptoAlgorithmIdentifier;

/* @formatter:off *//*

From RFC 3447:

    When rsaEncryption is used in an AlgorithmIdentifier the
    parameters MUST be present and MUST be NULL.

*//* @formatter:on */

/**
 * Algorithm identifier for RSA encryption.
 *
 * @link http://www.oid-info.com/get/1.2.840.113549.1.1.1
 * @link https://tools.ietf.org/html/rfc3447#appendix-C
 */
class RSAEncryptionAlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    AsymmetricCryptoAlgorithmIdentifier
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_oid = self::OID_RSA_ENCRYPTION;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function name(): string
    {
        return "rsaEncryption";
    }
    
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
        return new self();
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
