<?php

use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase {
    public function testTokenLengthMatchesRequestedByteCount(): void {
        $byteOne = 16;
        $byteTwo = 64;
        $tokenOne = generateToken($byteOne);
        $tokenTwo = generateToken($byteTwo);

        $this->assertSame($byteOne * 2, strlen($tokenOne['token']));
        $this->assertSame($byteTwo * 2, strlen($tokenTwo['token']));
    }

    public function testGenerateRandomToken():void {
        $tokenOne   = generateToken();
        $tokenTwo   = generateToken();
        $tokenThree = generateToken();

        $this->assertNotSame($tokenOne, $tokenTwo);
        $this->assertNotSame($tokenOne, $tokenThree);
        $this->assertNotSame($tokenTwo, $tokenThree);
    }

    public function testExpirationTimeCorrectness(): void {
        $token = generateToken();

        $expiresAt = new DateTime($token['expiresAt']);
        $expected  = (new DateTime())->modify('+60 minute');

        $diffInSeconds = abs($expected->getTimestamp() - $expiresAt->getTimestamp());
        $this->assertLessThanOrEqual(2, $diffInSeconds);
    }

    public function testGenerateTokenWithDefaultParameters():void {
        $token = generateToken();

        $this->assertSame(64, strlen($token['token']));
    }
}
