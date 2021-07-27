<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Asymmetric;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\Integer;
use ASN1\Type\Primitive\OctetString;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Asymmetric\ECPublicKeyAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AlgorithmIdentifierType;

/**
 * Implements PKCS #8 PrivateKeyInfo / OneAsymmetricKey ASN.1 type.
 *
 * @link https://tools.ietf.org/html/rfc5208#section-5
 * @link https://tools.ietf.org/html/rfc5958#section-2
 */
class OneAsymmetricKey
{
    /**
     * Version number for PrivateKeyInfo.
     *
     * @var integer
     */
    const VERSION_1 = 0;
    
    /**
     * Version number for OneAsymmetricKey.
     *
     * @var integer
     */
    const VERSION_2 = 1;
    
    /**
     * Version number.
     *
     * @var int
     */
    protected $_version;
    
    /**
     * Algorithm identifier.
     *
     * @var AlgorithmIdentifierType $_algo
     */
    protected $_algo;
    
    /**
     * Private key data.
     *
     * @var string $_privateKey
     */
    protected $_privateKeyData;
    
    /**
     * Constructor.
     *
     * @param AlgorithmIdentifierType $algo Algorithm
     * @param string $key Private key data
     */
    public function __construct(AlgorithmIdentifierType $algo, string $key)
    {
        $this->_version = self::VERSION_1;
        $this->_algo = $algo;
        $this->_privateKeyData = $key;
    }
    
    /**
     * Initialize from ASN.1.
     *
     * @param Sequence $seq
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromASN1(Sequence $seq): self
    {
        $version = $seq->at(0)
            ->asInteger()
            ->intNumber();
        if (!in_array($version, [self::VERSION_1, self::VERSION_2])) {
            throw new \UnexpectedValueException(
                "Version $version not supported.");
        }
        $algo = AlgorithmIdentifier::fromASN1($seq->at(1)->asSequence());
        $key = $seq->at(2)
            ->asOctetString()
            ->string();
        // @todo parse attributes and public key
        $obj = new static($algo, $key);
        $obj->_version = $version;
        return $obj;
    }
    
    /**
     * Initialize from DER data.
     *
     * @param string $data
     * @return self
     */
    public static function fromDER(string $data): self
    {
        return self::fromASN1(UnspecifiedType::fromDER($data)->asSequence());
    }
    
    /**
     * Initialize from a PrivateKey.
     *
     * @param PrivateKey $private_key
     * @return self
     */
    public static function fromPrivateKey(PrivateKey $private_key): self
    {
        return new static($private_key->algorithmIdentifier(),
            $private_key->toDER());
    }
    
    /**
     * Initialize from PEM.
     *
     * @param PEM $pem
     * @throws \UnexpectedValueException If PEM type is not supported
     * @return self
     */
    public static function fromPEM(PEM $pem): self
    {
        switch ($pem->type()) {
            case PEM::TYPE_PRIVATE_KEY:
                return self::fromDER($pem->data());
            case PEM::TYPE_RSA_PRIVATE_KEY:
                return self::fromPrivateKey(
                    RSA\RSAPrivateKey::fromDER($pem->data()));
            case PEM::TYPE_EC_PRIVATE_KEY:
                return self::fromPrivateKey(
                    EC\ECPrivateKey::fromDER($pem->data()));
        }
        throw new \UnexpectedValueException("Invalid PEM type.");
    }
    
    /**
     * Get algorithm identifier.
     *
     * @return AlgorithmIdentifierType
     */
    public function algorithmIdentifier(): AlgorithmIdentifierType
    {
        return $this->_algo;
    }
    
    /**
     * Get private key data.
     *
     * @return string
     */
    public function privateKeyData(): string
    {
        return $this->_privateKeyData;
    }
    
    /**
     * Get private key.
     *
     * @throws \RuntimeException
     * @return PrivateKey
     */
    public function privateKey(): PrivateKey
    {
        $algo = $this->algorithmIdentifier();
        switch ($algo->oid()) {
            // RSA
            case AlgorithmIdentifier::OID_RSA_ENCRYPTION:
                return RSA\RSAPrivateKey::fromDER($this->_privateKeyData);
            // elliptic curve
            case AlgorithmIdentifier::OID_EC_PUBLIC_KEY:
                $pk = EC\ECPrivateKey::fromDER($this->_privateKeyData);
                // NOTE: OpenSSL strips named curve from ECPrivateKey structure
                // when serializing into PrivateKeyInfo. However RFC 5915 dictates
                // that parameters (NamedCurve) must always be included.
                // If private key doesn't encode named curve, assign from parameters.
                if (!$pk->hasNamedCurve()) {
                    if (!$algo instanceof ECPublicKeyAlgorithmIdentifier) {
                        throw new \UnexpectedValueException(
                            "Not an EC algorithm.");
                    }
                    $pk = $pk->withNamedCurve($algo->namedCurve());
                }
                return $pk;
        }
        throw new \RuntimeException(
            "Private key " . $algo->name() . " not supported.");
    }
    
    /**
     * Get public key info corresponding to the private key.
     *
     * @return PublicKeyInfo
     */
    public function publicKeyInfo(): PublicKeyInfo
    {
        return $this->privateKey()
            ->publicKey()
            ->publicKeyInfo();
    }
    
    /**
     * Generate ASN.1 structure.
     *
     * @return Sequence
     */
    public function toASN1(): Sequence
    {
        $elements = array(new Integer($this->_version), $this->_algo->toASN1(),
            new OctetString($this->_privateKeyData));
        // @todo decode attributes and public key
        return new Sequence(...$elements);
    }
    
    /**
     * Generate DER encoding.
     *
     * @return string
     */
    public function toDER(): string
    {
        return $this->toASN1()->toDER();
    }
    
    /**
     * Generate PEM.
     *
     * @return PEM
     */
    public function toPEM(): PEM
    {
        return new PEM(PEM::TYPE_PRIVATE_KEY, $this->toDER());
    }
}
