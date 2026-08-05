<?php

use PHPUnit\Framework\TestCase;

final class AuthInputVAlidatorTest extends TestCase {
    private static array $rules;

    public static function setUpBeforeClass(): void {
        self::$rules = require __DIR__ . '/../../../src/config/validation.php';
    }

    public function testValidateUsernameValidName(): void {
        $this->assertSame(null, AuthInputValidator::validateUsername('yuko'));
    }

    public function testValidateUsernameRejectsEmpty(): void {
        $this->assertSame(
            self::$rules['username']['messages']['required'],
            AuthInputValidator::validateUsername('')
        );
    }

    public function testValidateUsernameMinLength(): void {
        $validName = str_repeat('a', self::$rules['username']['minLength']);
        $this->assertSame(null, AuthInputValidator::validateUsername($validName));
    }

    public function testValidateUsernameMaxLength(): void {
        $validName = str_repeat('a', self::$rules['username']['maxLength']);
        $this->assertSame(null, AuthInputValidator::validateUsername($validName));
    }

    public function testValidateUsernameRejectTooShort(): void {
        $tooShortName = str_repeat('a', self::$rules['username']['minLength'] - 1);
        $this->assertSame(
            self::$rules['username']['messages']['length'],
            AuthInputValidator::validateUsername($tooShortName)
        );
    }

    public function testValidateUsernameRejectTooLong(): void {
        $tooLongName = str_repeat('a', self::$rules['username']['maxLength'] + 1);
        $this->assertSame(
            self::$rules['username']['messages']['length'],
            AuthInputValidator::validateUsername($tooLongName)
        );
    }

    public function testValidateUsernameRejectInvalidChars(): void {
        $this->assertSame(
            self::$rules['username']['messages']['pattern'],
            AuthInputValidator::validateUsername('bad name!')
        );
    }

    public function testValidateEmailValidEmail(): void {
        $this->assertSame(null, AuthInputValidator::validateEmail('yuko@example.com'));
    }

    public function testValidateEmailRejectEmpty(): void {
        $this->assertSame(
            self::$rules['email']['messages']['required'],
            AuthInputValidator::validateEmail('')
        );
    }

    public function testValidateEmailRejectMissingAtSymbol(): void {
        $this->assertSame(
            self::$rules['email']['messages']['pattern'],
            AuthInputValidator::validateEmail('example.email.com')
        );
    }

    public function testValidateEmailRejectMissingLocalPart(): void {
        $this->assertSame(
            self::$rules['email']['messages']['pattern'],
            AuthInputValidator::validateEmail('@example')
        );
    }

    public function testValidateEmailRejectNoDomain(): void {
        $this->assertSame(
            self::$rules['email']['messages']['pattern'],
            AuthInputValidator::validateEmail('yuko@')
        );
    }

    public function testValidateEmailRejectSpaces(): void {
        $this->assertSame(
            self::$rules['email']['messages']['pattern'],
            AuthInputValidator::validateEmail('yu ko@example.com')
        );
    }

    public function testValidateEmailMaxLength(): void {
        $validEmail = str_repeat('a', self::$rules['email']['maxLength'] - 10) . '@email.com';
        $this->assertSame(null, AuthInputValidator::validateEmail($validEmail));
    }

    public function testValidateEmailRejectTooLong(): void {
        $tooLongEmail = str_repeat('a', self::$rules['email']['maxLength'] - 9) . '@email.com';
        $this->assertSame(
            self::$rules['email']['messages']['length'],
            AuthInputValidator::validateEmail($tooLongEmail)
        );
    }

    public function testValidatePasswordValidPassword(): void {
        $this->assertSame(null, AuthInputValidator::validatePassword('Ab123-<>cccccc'));
    }

    public function testValidatePasswordRejectEmpty(): void {
        $this->assertSame(
            self::$rules['password']['messages']['required'],
            AuthInputValidator::validatePassword('')
        );
    }

    public function testValidatePasswordMinLength(): void {
        $validPassword = 'Aa1-' . str_repeat('a', self::$rules['password']['minLength'] - 4);
        $this->assertSame(null, AuthInputValidator::validatePassword($validPassword));
    }

    public function testValidatePasswordMaxLength(): void {
        $validPassword = 'Aa1-' . str_repeat('a', self::$rules['password']['maxLength'] - 4);
        $this->assertSame(null, AuthInputValidator::validatePassword($validPassword));
    }

    public function testValidatePasswordRejectTooShort(): void {
        $tooShortPassword = 'Aa1-' . str_repeat('a', self::$rules['password']['minLength'] - 5);
        $this->assertSame(
            self::$rules['password']['messages']['length'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }

    public function testValidatePasswordRejectTooLong(): void {
        $tooShortPassword = 'Aa1-' . str_repeat('a', self::$rules['password']['maxLength'] - 3);
        $this->assertSame(
            self::$rules['password']['messages']['length'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }

    public function testValidatePasswordRejectMissingLowercase(): void {
        $tooShortPassword = 'AA1-' . str_repeat('A', self::$rules['password']['minLength']);
        $this->assertSame(
            self::$rules['password']['messages']['pattern'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }
    
    public function testValidatePasswordRejectMissingUppercase(): void {
        $tooShortPassword = 'aa1-' . str_repeat('a', self::$rules['password']['minLength']);
        $this->assertSame(
            self::$rules['password']['messages']['pattern'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }

    public function testValidatePasswordRejectMissingNum(): void {
        $tooShortPassword = 'Aab-' . str_repeat('a', self::$rules['password']['minLength']);
        $this->assertSame(
            self::$rules['password']['messages']['pattern'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }

    public function testValidatePasswordRejectMissingSpecialChar(): void {
        $tooShortPassword = 'Aa12' . str_repeat('a', self::$rules['password']['minLength']);
        $this->assertSame(
            self::$rules['password']['messages']['pattern'],
            AuthInputValidator::validatePassword($tooShortPassword)
        );
    }
}
