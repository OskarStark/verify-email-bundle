<?php

namespace SymfonyCasts\Bundle\VerifyUser;

use SymfonyCasts\Bundle\VerifyUser\Generator\TokenGenerator;
use SymfonyCasts\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifyHelper implements VerifyHelperInterface
{
    /**
     * @var TokenGenerator
     */
    private $generator;

    /**
     * @var int The length of time in seconds that a signed URI is valid for after it is created
     */
    private $lifetime;

    public function __construct(TokenGenerator $generator, int $lifetime)
    {
        $this->generator = $generator;
        $this->lifetime = $lifetime;
    }

    /**
     * @inheritDoc
     */
    public function generateSignature(string $userId, \DateTimeInterface $expiresAt = null): SignatureComponents
    {
        if (null === $expiresAt) {
            $expiresAt = (new \DateTimeImmutable('now'))
                ->modify(sprintf('+%d seconds', 450));
        }

        return new SignatureComponents($expiresAt, $this->generator->getToken($expiresAt, $userId));
    }

    /**
     * @inheritDoc
     */
    public function isValidSignature(string $signature, string $userId): bool
    {
        $timestamp = (int) substr($signature, 0, 10);
        $time = new \DateTimeImmutable();
        $expiresAt = $time->setTimestamp($timestamp);

        $expected = $this->generator->getToken($expiresAt, $userId);

        return hash_equals($expected, substr($signature, 10));
    }

    /**
     * @inheritDoc
     */
    public function getSignatureLifetime(): int
    {
        return $this->lifetime;
    }
}