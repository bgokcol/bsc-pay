<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Hash;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Primitive\NullType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\HashAlgorithmIdentifier;

/* @formatter:off *//*

From RFC 1321 - 1. Executive Summary

    In the X.509 type AlgorithmIdentifier, the parameters for MD5
    should have type NULL.

From RFC 3370 - 2.2 MD5

    The AlgorithmIdentifier parameters field MUST be present, and the
    parameters field MUST contain NULL.  Implementations MAY accept the
    MD5 AlgorithmIdentifiers with absent parameters as well as NULL
    parameters.

*//* @formatter:on */

/**
 * MD5 algorithm identifier.
 *
 * @link http://oid-info.com/get/1.2.840.113549.2.5
 * @link https://tools.ietf.org/html/rfc1321#section-1
 * @link https://tools.ietf.org/html/rfc3370#section-2.2
 */
class MD5AlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    HashAlgorithmIdentifier
{
    /**
     * Parameters.
     *
     * @var NullType|null $_params
     */
    protected $_params;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_oid = self::OID_MD5;
        $this->_params = new NullType();
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function name(): string
    {
        return "md5";
    }
    
    /**
     *
     * @param UnspecifiedType $params
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        $obj = new static();
        // if parameters field is present, it must be null type
        if (isset($params)) {
            $obj->_params = $params->asNull();
        }
        return $obj;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return NullType|null
     */
    protected function _paramsASN1()
    {
        return $this->_params;
    }
}
