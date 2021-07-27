<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Signature;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Primitive\NullType;

/* @formatter:off *//*

From RFC 4055 - 5.  PKCS #1 Version 1.5 Signature Algorithm

   When any of these four object identifiers appears within an
   AlgorithmIdentifier, the parameters MUST be NULL.  Implementations
   MUST accept the parameters being absent as well as present.

*//* @formatter:on */

/**
 * Base class for RSA signature algorithms specified in RFC 4055.
 *
 * @link https://tools.ietf.org/html/rfc4055#section-5
 */
abstract class RFC4055RSASignatureAlgorithmIdentifier extends RSASignatureAlgorithmIdentifier
{
    /**
     * Parameters.
     *
     * @var \ASN1\Element|null $_params
     */
    protected $_params;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_params = new NullType();
    }
    
    /**
     *
     * @param UnspecifiedType $params
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        $obj = new static();
        // store parameters so re-encoding doesn't change
        if (isset($params)) {
            $obj->_params = $params->asElement();
        }
        return $obj;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    protected function _paramsASN1()
    {
        return $this->_params;
    }
}
