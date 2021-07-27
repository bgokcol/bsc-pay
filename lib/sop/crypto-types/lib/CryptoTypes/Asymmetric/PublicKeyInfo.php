<?php

declare(strict_types = 1);

namespace Sop\CryptoTypes\Asymmetric;

use ASN1\Type\UnspecifiedType;
use ASN1\Type\Constructed\Sequence;
use ASN1\Type\Primitive\BitString;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Asymmetric\ECPublicKeyAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AlgorithmIdentifierType;

/**
 * Implements X.509 SubjectPublicKeyInfo ASN.1 type.
 *
 * @link https://tools.ietf.org/html/rfc5280#section-4.1
 */
class PublicKeyInfo
{
    /**
     * Algorithm identifier.
     *
     * @var AlgorithmIdentifierType $_algo
     */
    protected $_algo;
    
    /**
     * Public key data.
     *
     * @var string $_publicKeyData
     */
    protected $_publicKeyData;
    
    /**
     * Constructor.
     *
     * @param AlgorithmIdentifierType $algo Algorithm
     * @param string $key Public key data
     */
    public function __construct(AlgorithmIdentifierType $algo, string $key)
    {
        $this->_algo = $algo;
        $this->_publicKeyData = $key;
    }
    
    /**
     * Initialize from ASN.1.
     *
     * @param Sequence $seq
     * @return self
     */
    public static function fromASN1(Sequence $seq): self
    {
        $algo = AlgorithmIdentifier::fromASN1($seq->at(0)->asSequence());
        $key = $seq->at(1)
            ->asBitString()
            ->string();
        return new self($algo, $key);
    }
    
    /**
     * Inititalize from a PublicKey.
     *
     * @param PublicKey $public_key
     * @return self
     */
    public static function fromPublicKey(PublicKey $public_key): self
    {
        return new self($public_key->algorithmIdentifier(),
            $public_key->subjectPublicKeyData());
    }
    
    /**
     * Initialize from PEM.
     *
     * @param PEM $pem
     * @throws \UnexpectedValueException
     * @return self
     */
    public static function fromPEM(PEM $pem): self
    {
        switch ($pem->type()) {
            case PEM::TYPE_PUBLIC_KEY:
                return self::fromDER($pem->data());
            case PEM::TYPE_RSA_PUBLIC_KEY:
                return RSA\RSAPublicKey::fromDER($pem->data())->publicKeyInfo();
        }
        throw new \UnexpectedValueException("Invalid PEM type.");
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
     * Get algorithm identifier.
     *
     * @return AlgorithmIdentifierType
     */
    public function algorithmIdentifier(): AlgorithmIdentifierType
    {
        return $this->_algo;
    }
    
    /**
     * Get public key data.
     *
     * @return string
     */
    public function publicKeyData(): string
    {
        return $this->_publicKeyData;
    }
    
    /**
     * Get public key.
     *
     * @throws \RuntimeException
     * @return PublicKey
     */
    public function publicKey(): PublicKey
    {
        $algo = $this->algorithmIdentifier();
        switch ($algo->oid()) {
            // RSA
            case AlgorithmIdentifier::OID_RSA_ENCRYPTION:
                return RSA\RSAPublicKey::fromDER($this->_publicKeyData);
            // elliptic curve
            case AlgorithmIdentifier::OID_EC_PUBLIC_KEY:
                if (!$algo instanceof ECPublicKeyAlgorithmIdentifier) {
                    throw new \UnexpectedValueException("Not an EC algorithm.");
                }
                // ECPoint is directly mapped into public key data
                return new EC\ECPublicKey($this->_publicKeyData,
                    $algo->namedCurve());
        }
        throw new \RuntimeException(
            "Public key " . $algo->name() . " not supported.");
    }
    
    /**
     * Get key identifier using method 1 as described by RFC 5280.
     *
     * @link https://tools.ietf.org/html/rfc5280#section-4.2.1.2
     * @return string 20 bytes (160 bits) long identifier
     */
    public function keyIdentifier(): string
    {
        return sha1($this->_publicKeyData, true);
    }
    
    /**
     * Get key identifier using method 2 as described by RFC 5280.
     *
     * @link https://tools.ietf.org/html/rfc5280#section-4.2.1.2
     * @return string 8 bytes (64 bits) long identifier
     */
    public function keyIdentifier64(): string
    {
        $id = substr($this->keyIdentifier(), -8);
        $c = (ord($id[0]) & 0x0f) | 0x40;
        $id[0] = chr($c);
        return $id;
    }
    
    /**
     * Generate ASN.1 structure.
     *
     * @return Sequence
     */
    public function toASN1(): Sequence
    {
        return new Sequence($this->_algo->toASN1(),
            new BitString($this->_publicKeyData));
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
        return new PEM(PEM::TYPE_PUBLIC_KEY, $this->toDER());
    }
}
