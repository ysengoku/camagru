<?php

final class ProfileServiceTest extends DbTestCase {
    private function createUser(string $username, string $email, string $password): void {
        $data = new SignupData($username, $email, $password);
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();
    }

    private function profileDataFactory(User $user, array $newData = []) {
        $username             = $newData['username'] ?? $user->username;
        $email                = $newData['email'] ?? $user->email;
        $password             = $newData['password'] ?? null;
        $newPassword          = $newData['newPassword'] ?? null;
        $avatar               = $newData['avatar'] ?? $user->avatar;
        $notificationsEnabled = $newData['notificationsEnabled'] ?? $user->email_notifications_enabled;

        return new ProfileData($username, $email, $password, $newPassword, $avatar, $notificationsEnabled);
    }

    // ===== updateProfile() - username / notifications =======================

    public function testUpdateProfileUsernameAndNotificationsChangeSucceeds(): void {
        $username = 'success';
        $email    = 'success@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user           = User::findByUsername($username);
        $newUsername    = 'newname';
        $newProfileData = $this->profileDataFactory($user, ['username' => $newUsername, 'notificationsEnabled' => 0]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertFalse($result->data['emailVerificationRequired']);

        $updatedUser = User::findByEmail($email);
        $this->assertSame($newUsername, $updatedUser->username);
        $this->assertSame(0, $updatedUser->email_notifications_enabled);
    }

    // ===== updateProfile() - avatar ==========================================

    public function testUpdateProfileAvatarChangeSucceeds(): void {
        $username = 'avatarchange';
        $email    = 'avatarchange@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);
        
        $user = User::findByUsername($username);
        $avatarPath = '/media/avatar.jpg';
        $newAvatarPath  = '/media/new-avatar.jpg';

        $postOne = new Post($avatarPath, $user->id);
        $postOne->save();
        $postTwo = new Post($newAvatarPath, $user->id);
        $postTwo->save();

        $newProfileData = $this->profileDataFactory($user, ['avatar' => $newAvatarPath]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertNotSame('', $result->data['avatarHtml']);

        $updatedUser = User::findByUsername($username);
        $this->assertNotSame($updatedUser->avatar, $avatarPath);
        $this->assertSame($updatedUser->avatar, $newAvatarPath);

        $resubmitResult = ProfileService::getInstance()->updateProfile($newProfileData, $user);
        $this->assertTrue($resubmitResult->success);
        $this->assertSame('', $resubmitResult->data['avatarHtml']);
    }

    // ===== updateProfile() - password ========================================

    public function testUpdateProfilePasswordChangeSucceeds(): void {
        $username = 'passwordchange';
        $email    = 'passwordchange@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user           = User::findByUsername($username);
        $newPassword    = 'New-Valid-Password123!';
        $newProfileData = $this->profileDataFactory($user, ['password' => $password, 'newPassword' => $newPassword]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);

        $loginWithOldPassword = SessionService::getInstance()->processLogin($username, $password);
        $this->assertFalse($loginWithOldPassword->success);
        $loginWithNewPassword = SessionService::getInstance()->processLogin($username, $newPassword);
        $this->assertTrue($loginWithNewPassword->success);
    }

    // ===== updateProfile() - email ===========================================

    public function testUpdateProfileEmailChangeSucceeds(): void {
        $username = 'emailchange';
        $email    = 'emailchange@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user           = User::findByUsername($username);
        $newEmail       = 'new-email@example.com';
        $newProfileData = $this->profileDataFactory($user, ['email' => $newEmail, 'password' => $password]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertTrue($result->data['emailVerificationRequired']);

        $updatedUser = User::findByEmail($email);
        $this->assertNotSame($newEmail, $updatedUser->email);
        $this->assertSame($email, $updatedUser->email);
        $this->assertSame($newEmail, $updatedUser->pending_email);
        $this->assertNotNull($updatedUser->email_verification_token);
    }

    // ===== updateProfile() - validation rejects ==============================

    public function testUpdateProfileRejectUsernameInvalidFormat(): void {
        $username = 'invalidusername';
        $email    = 'invalidusername@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $invalidUsername = 'invalid%%%';
        $newProfileData  = $this->profileDataFactory($user, ['username' => $invalidUsername]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['username']);
    }

    public function testUpdateProfileRejectUsernameUnavailable(): void {
        $username = 'unavailableusername';
        $email    = 'unavailableusername@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);
        
        $unavailableUsername = 'yuko';
        $this->createUser($unavailableUsername, 'yuko@example.com', $password);

        $user            = User::findByUsername($username);
        $newProfileData  = $this->profileDataFactory($user, ['username' => $unavailableUsername]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['username']);
    }

    public function testUpdateProfileRejectEmailInvalidFormat(): void {
        $username = 'invalidemail';
        $email    = 'invalidemail@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $invalidEmail    = 'invalidemail.example@';
        $newProfileData  = $this->profileDataFactory($user, ['email' => $invalidEmail, 'password' => $password]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['email']);
    }

    public function testUpdateProfileRejectEmailUnavailable(): void {
        $username = 'unavailableemail';
        $email    = 'unavailableemail@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);
        
        $unavailableEmail = 'alreadyused@example.com';
        $this->createUser('alreadyused', $unavailableEmail, $password);

        $user            = User::findByUsername($username);
        $newProfileData  = $this->profileDataFactory($user, ['email' => $unavailableEmail, 'password' => $password]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['email']);
    }

    public function testUpdateProfileRejectEmailMissingCurrentPassword(): void {
        $username = 'emailmissingpassword';
        $email    = 'emailmissingpassword@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $newEmail        = 'newemail@example.com';
        $newProfileData  = $this->profileDataFactory($user, ['email' => $newEmail]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['general']);
    }

    public function testUpdateProfileRejectEmailWrongCurrentPassword(): void {
        $username = 'emailwrongpassword';
        $email    = 'emailwrongpassword@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $newEmail        = 'newemail@example.com';
        $wrongPassword   = 'Wrong-Password123!';
        $newProfileData  = $this->profileDataFactory($user, ['email' => $newEmail, 'password' => $wrongPassword]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['general']);
    }

    public function testUpdateProfileRejectAvatarOwnedByOtherUser(): void {
        $username = 'avatarnotmine';
        $email    = 'avatarnotmine@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $otherUsername  = 'other';
        $otherUserEmail = 'other@example.com';
        $this->createUser($otherUsername, $otherUserEmail, $password);
        
        $user      = User::findByUsername($username);
        $otherUser = User::findByUsername($otherUsername);

        $myAvatarPath     = '/media/myavatar.jpg';
        $otherAvatarPath  = '/media/other-avatar.jpg';

        $myPost = new Post($myAvatarPath, $user->id);
        $myPost->save();
        $otherPost = new Post($otherAvatarPath, $otherUser->id);
        $otherPost->save();

        $newProfileData = $this->profileDataFactory($user, ['avatar' => $otherAvatarPath]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['avatar']);
    }

    public function testUpdateProfileRejectAvatarNonexistentPath(): void {
        $username = 'nonexistentavatar';
        $email    = 'nonexistentavatar@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user                  = User::findByUsername($username);
        $nonexistentAvatarPath = '/media/nonexistentavatar.jpg';
        $newProfileData        = $this->profileDataFactory($user, ['avatar' => $nonexistentAvatarPath]);
        $result                = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['avatar']);
    }

    public function testUpdateProfileRejectNewPasswordInvalidFormat(): void {
        $username = 'invalidpassword';
        $email    = 'invalidpassword@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $invalidpassword = 'invalid';
        $newProfileData  = $this->profileDataFactory($user, ['password' => $password, 'newPassword' => $invalidpassword]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['password']);
    }

    public function testUpdateProfileRejectNewpasswordMissingCurrentPassword(): void {
        $username = 'missingpassword';
        $email    = 'missingpassword@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $newPassword     = 'New-Valid-Password123!';
        $newProfileData  = $this->profileDataFactory($user, ['newPassword' => $newPassword]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['general']);
    }

    public function testUpdateProfileRejectNewpasswordWrongCurrentPassword(): void {
        $username = 'wrongpassword';
        $email    = 'wrongpassword@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user            = User::findByUsername($username);
        $newPassword     = 'New-Valid-Password123!';
        $wrongPassword   = 'Wrong-Password123!';
        $newProfileData  = $this->profileDataFactory($user, ['password' => $wrongPassword, 'newPassword' => $newPassword]);
        $result          = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertFalse($result->success);
        $this->assertNotNull($result->errors['general']);
    }

    // ===== updateProfile() - unchanged values ================================

    public function testUpdateProfileUsernameUnchanged(): void {
        $username = 'myusername';
        $email    = 'myusername@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user           = User::findByUsername($username);
        $newProfileData = $this->profileDataFactory($user, ['username' => $username]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertFalse($result->data['emailVerificationRequired']);

        $updatedUser = User::findByEmail($email);
        $this->assertSame($username, $updatedUser->username);
    }

    public function testUpdateProfileUsernameUnchangedDifferentCase(): void {
        $username = 'myusername';
        $email    = 'myusername@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user             = User::findByUsername($username);
        $capitalUsername  = 'Myusername';
        $newProfileData   = $this->profileDataFactory($user, ['username' => $capitalUsername]);
        $result           = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertFalse($result->data['emailVerificationRequired']);

        $updatedUser = User::findByEmail($email);
        $this->assertSame($capitalUsername, $updatedUser->username);
    }

    public function testUpdateProfileEmailUnchanged(): void {
        $username = 'myemail';
        $email    = 'myemail@example.com';
        $password = 'Valid-Password123!';
        $this->createUser($username, $email, $password);

        $user           = User::findByUsername($username);
        $newProfileData = $this->profileDataFactory($user, ['email' => $email]);
        $result         = ProfileService::getInstance()->updateProfile($newProfileData, $user);

        $this->assertTrue($result->success);
        $this->assertFalse($result->data['emailVerificationRequired']);

        $updatedUser = User::findByUsername($username);
        $this->assertSame($email, $updatedUser->email);
    }
}
