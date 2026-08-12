<?php

use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase {
    public function testRenderEmailTemplateIncludesLinkAndTitle(): void {
        $html = renderEmailTemplate('verification', [
            'message' => 'Please confirm your account.',
            'verificationLink' => 'https://example.com/verify?token=abc123',
            'emailTitle' => 'Verify your email',
            'logoUrl' => 'https://example.com/logo.png',
        ]);

        $this->assertStringContainsString('https://example.com/verify?token=abc123', $html);
        $this->assertStringContainsString('Verify your email', $html);
    }

    public function testRenderEmailTemplateSelectsRequestedTemplate(): void {
        $html = renderEmailTemplate('forgotPassword', [
            'resetLink' => 'https://example.com/reset?token=xyz789',
            'username' => 'testuser',
        ]);

        $this->assertStringContainsString('Reset Password', $html);
        $this->assertStringNotContainsString('Verify Email Address', $html);
    }

    public function testRenderEmailTemplateWithoutVarsDoesNotThrow(): void {
        $html = renderEmailTemplate('verification');

        $this->assertNotSame('', $html);
    }

    public function testRenderEmailTemplateWrapsContentInSharedLayout(): void {
        $html = renderEmailTemplate('verification', [
            'verificationLink' => 'https://example.com/verify',
        ]);

        $footerContent = 'If you did not create a Camagru account, you can safely ignore this email.';
        $this->assertStringContainsString($footerContent, $html);
    }
}
