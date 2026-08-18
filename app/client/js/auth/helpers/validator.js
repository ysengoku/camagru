import { rules } from './validationRules.js';

export class Validator {
  constructor() {
    this.validateUsername = this.validateUsername.bind(this);
    this.validateEmail = this.validateEmail.bind(this);
    this.validatePassword = this.validatePassword.bind(this);
  }

  validateUsername(username) {
    const minLength = rules.username.minLength;
    const maxLength = rules.username.maxLength;
    const regex = new RegExp(rules.username.pattern);

    if (!username) {
      return {
        valid: false,
        message: rules.username.messages.required,
      };
    }
    if (username.length < minLength || username.length > maxLength) {
      return {
        valid: false,
        message: `Username must be between ${minLength} and ${maxLength} characters long`,
      };
    }
    if (!regex.test(username)) {
      return {
        valid: false,
        message: rules.username.messages.pattern,
      };
    }
    return { valid: true };
  }

  validateEmail(email) {
    const maxLength = rules.email.maxLength;
    const regex = new RegExp(rules.email.pattern);

    if (!email) {
      return {
        valid: false,
        message: rules.email.messages.required,
      };
    }
    if (email.length > maxLength) {
      return {
        valid: false,
        message: `Email must not exceed ${maxLength} characters`,
      };
    }
    if (!regex.test(email)) {
      return { valid: false, message: rules.email.messages.pattern };
    }
    return { valid: true };
  }

  validatePassword(password, passwordRepeat) {
    const minLength = rules.password.minLength;
    const maxLength = rules.password.maxLength;
    const requireLower = rules.password.requireLower;
    const requireUpper = rules.password.requireUpper;
    const requireDigit = rules.password.requireDigit;
    const specialCharRegex = new RegExp(rules.password.specialCharPattern);

    if (!password || !passwordRepeat) {
      return {
        valid: false,
        message: rules.password.messages.required,
      };
    }

    if (password !== passwordRepeat) {
      return {
        valid: false,
        message: rules.password.messages.match,
      };
    }

    if (password.length < minLength || password.length > maxLength) {
      return {
        valid: false,
        message: `Password must be between ${minLength} and ${maxLength} characters long`,
      };
    }

    if (
      (requireLower && !/[a-z]/.test(password)) ||
      (requireUpper && !/[A-Z]/.test(password)) ||
      (requireDigit && !/[0-9]/.test(password)) ||
      !specialCharRegex.test(password)
    ) {
      return {
        valid: false,
        message: rules.password.messages.pattern,
      };
    }
    return { valid: true };
  }
}

export const validator = new Validator();
