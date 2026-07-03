<?php

final class VerifyEmailService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function processVerification(string $token): ServiceResult {
        if (empty($token)) {
            return ServiceResult::failure(['token' => 'Verification token is required.', 'status' => Response::BAD_REQUEST]);
        }

        $user = User::findByVerificationToken($token);
        if ($user === null) {
            return ServiceResult::failure(['token' => 'Invalid verification token.', 'status' => Response::NOT_FOUND]);
        }

        if ($user->email_verified) {
            return ServiceResult::success(['message' => 'Email is already verified.']);
        }

        $expiresAt = $user->verification_token_expires_at;
        if ($expiresAt === null || new DateTime($expiresAt) < new DateTime()) {
            $user->delete();
            return ServiceResult::failure(['token' => 'Verification link has expired.', 'status' => Response::BAD_REQUEST]);
        }

        $user->email_verified = 1;
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        if (!$user->save()) {
            return ServiceResult::failure(['email' => 'Failed to verify email.', 'status' => Response::INTERNAL_ERROR]);
        }
        return ServiceResult::success(['message' => 'Email verified successfully.']);
    }
}