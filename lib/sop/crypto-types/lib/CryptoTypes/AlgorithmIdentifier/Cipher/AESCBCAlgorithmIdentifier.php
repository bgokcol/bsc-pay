<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\AlgorithmIdentifier\Cipher;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Primitive\OctetString;

/* @formatter:off *//*

From RFC 3565 - 4.1. AES Algorithm Identifiers and Parameters:

   The AlgorithmIdentifier parameters field MUST be present, and the
   parameters field MUST contain a AES-IV:

       AES-IV ::= OCTET STRING (SIZE(16))

*//* @formatter:on */

/**
 * Base class for AES-CBC algorithm identifiers.
 *
 * @link https://tools.ietf.org/html/rfc3565.html#section-4.1
 */
abstract class AESCBCAlgorithmIdentifier extends BlockCipherAlgorithmIdentifier
{
    /**
     * Constructor.
     *
     * @param string|null $iv Initialization vector
     */
    public function __construct($iv = null)
    {
        $this->_checkIVSize($iv);
        $this->_initializationVector = $iv;
    }
    
    /**
     *
     * @param UnspecifiedType $params
     * @throws \UnexpectedValueException If parameters are invalid
     * @return self
     */
    public static function fromASN1Params(UnspecifiedType $params = null)
    {
        if (!isset($params)) {
            throw new \UnexpectedValueException("No parameters.");
        }
        $iv = $params->asOctetString()->string();
        return new static($iv);
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @return OctetString
     */
    protected function _paramsASN1()
    {
        if (!isset($this->_initializationVector)) {
            throw new \LogicException("IV not set.");
        }
        return new OctetString($this->_initializationVector);
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function blockSize(): int
    {
        return 16;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     */
    public function ivSize(): int
    {
        return 16;
    }
}
