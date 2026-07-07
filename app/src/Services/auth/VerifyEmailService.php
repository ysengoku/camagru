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
        
        $expiresAt = $user->email_verification_token_expires_at;
        if ($expiresAt === null || new DateTime($expiresAt) < new DateTime()) {
            $user->delete();
            return ServiceResult::failure(['token' => 'Verification link has expired.', 'status' => Response::BAD_REQUEST]);
        }

        if ($user->email_verified === 1) {
            if ($user->pending_email === null) {
                return ServiceResult::failure(['token' => 'No pending email verification found.', 'status' => Response::BAD_REQUEST]);
            }

            $user->email = $user->pending_email;
            $user->pending_email = null;
        }

        $user->email_verified = 1;            
        $user->email_verification_token = null;
        $user->email_verification_token_expires_at = null;
        if (!$user->save()) {
            return ServiceResult::failure(['email' => 'Failed to verify email.', 'status' => Response::INTERNAL_ERROR]);
        }
        return ServiceResult::success(['message' => 'Email verified successfully.']);
    }
}
