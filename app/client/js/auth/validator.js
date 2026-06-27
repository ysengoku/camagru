import { rules } from './validationRules.js';

class Validator {
  constructor() {
    this.validateUsername = this.validateUsername.bind(this);
    this.validateEmail = this.validateEmail.bind(this);
    this.validatePassword = this.validatePassword.bind(this);
  }

  validateUsername(username) {
    const minLength = rules.username.minLength;
    const maxLength = rules.username.maxLength;
    const regex = new RegExp(rules.username.pattern);

    if (username.length < minLength) {
      return {
        valid: false,
        message: `Username must be at least ${minLength} characters long`,
      };
    }
    if (username.length > maxLength) {
      return {
        valid: false,
        message: `Username must not exceed ${maxLength} characters`,
      };
    }
    if (!regex.test(username)) {
      return {
        valid: false,
        message:
          'Username can only contain letters, numbers, underscore, and hyphen',
      };
    }
    return { valid: true };
  }

  validateEmail(email) {
    const maxLength = rules.email.maxLength;
    const regex = new RegExp(rules.email.pattern);

    if (email.length > maxLength) {
      return {
        valid: false,
        message: `Email must not exceed ${maxLength} characters`,
      };
    }
    if (!regex.test(email)) {
      return { valid: false, message: 'Email format is invalid' };
    }
    return { valid: true };
  }

  validatePassword(username, password, passwordRepeat) {
    const minLength = rules.password.minLength;
    const maxLength = rules.password.maxLength;

    if (password !== passwordRepeat) {
      return {
        valid: false,
        message: 'The password and password confirmation do not match',
      };
    }
    if (password.length < minLength) {
      return {
        valid: false,
        message: `Password must be at least ${minLength} characters long`,
      };
    }
    if (password.length > maxLength) {
      return {
        valid: false,
        message: `Password must not exceed ${maxLength} characters`,
      };
    }
    if (password.toLowerCase().includes(username.toLowerCase())) {
      return {
        valid: false,
        message: 'Password must not contain the username',
      };
    }
    if (
      !/[a-z]/.test(password) ||
      !/[A-Z]/.test(password) ||
      !/[0-9]/.test(password)
    ) {
      return {
        valid: false,
        message:
          'Password must contain at least one lowercase, one uppercase, and one digit',
      };
    }
    if (/[^a-zA-Z0-9]/.test(password)) {
      return {
        valid: false,
        message: 'Password may contain only alphanumeric characters',
      };
    }
    return { valid: true };
  }
}

export const validator = new Validator();
