<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Hash;

use ASN1\Type\UnspecifiedType;
use Sop\CryptoTypes\AlgorithmIdentifier\SpecificAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\HashAlgorithmIdentifier;

/* @formatter:off *//*

From RFC 3370 - 2.1 SHA-1

    The AlgorithmIdentifier parameters field is OPTIONAL.  If present,
    the parameters field MUST contain a NULL.  Implementations MUST
    accept SHA-1 AlgorithmIdentifiers with absent parameters.
    Implementations MUST accept SHA-1 AlgorithmIdentifiers with NULL
    parameters.  Implementations SHOULD generate SHA-1
    AlgorithmIdentifiers with absent parameters.

*//* @formatter:on */

/**
 * SHA-1 algorithm identifier.
 *
 * @link http://oid-info.com/get/1.3.14.3.2.26
 * @link https://tools.ietf.org/html/rfc3370#section-2.1
 */
class SHA1AlgorithmIdentifier extends SpecificAlgorithmIdentifier implements 
    HashAlgorithmIdentifier
{
    /**
     * Parameters.
     *
     * @var \ASN1\Type\Primitive\NullType|null $_params
     */
    protected $_params;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_oid = self::OID_SHA1;
        $this->_params = null;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function name(): string
    {
        return "sha1";
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
     * @return \ASN1\Type\Primitive\NullType|null
     */
    protected function _paramsASN1()
    {
        return $this->_params;
    }
}
