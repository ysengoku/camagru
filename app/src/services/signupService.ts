import { UserModel } from '../mvc';
import { EmailService } from './EmailService';
import { generateToken } from '../utils/crypto';
import bcrypt from 'bcrypt';

export async function signupService(data: {
  username: string;
  email: string;
  password: string;
  passwordRepeat: string;
}): Promise<{ success: boolean; message?: string | null }> {
  const { username, email, password, passwordRepeat } = data;
  if (!username || !email || !password || !passwordRepeat) {
    return { success: false, message: 'Missing required fields' };
  }

  // Validate user input values
  const validators = [
    validateUsername(username),
    validateEmail(email),
    validatePassword(username, password, passwordRepeat),
  ];
  const failed = validators.find((v) => !v.valid);
  if (failed) {
    return { success: false, message: failed.message };
  }

  try {
    // Verify uniqueness of username and email
    const usernameExists = await UserModel.findByKey('username', username);
    if (usernameExists) {
      return {
        success: false,
        message: 'User with this username exists already',
      };
    }
    const emailExists = await UserModel.findByKey('email', email);
    if (emailExists) {
      return { success: false, message: 'This email is already used' };
    }

    // Crreate a new user
    const hashedPassword = await bcrypt.hash(password, 10);
    const verificationToken = generateToken();
    const newUser = new UserModel();
    newUser.username = username;
    newUser.email = email;
    newUser.password_hash = hashedPassword;
    newUser.verification_token = verificationToken;
    await newUser.createUser();

    const emailService = EmailService.getInstance();
    const sent = await emailService.sendConfirmationEmail(newUser.email, newUser.verification_token);
    if (sent.success) {
      return { success: true };
    }
    return { success: false, message: sent.error };
  } catch (error) {
    console.error(error);
    return { success: false, message: 'Database error' };
  }
}

function validateUsername(username: string) {
  const minLength = 3;
  const maxLength = 20;
  const regex = /^[a-zA-Z0-9_-]+$/;

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
      message: 'Username can only contain letters, numbers, underscore, and hyphen',
    };
  }
  return { valid: true };
}

function validateEmail(email: string) {
  const maxLength = 254;
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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

function validatePassword(username: string, password: string, passwordRepeat: string) {
  const minLength = 12;
  const maxLength = 72;

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
    return { valid: false, message: 'Password must not contain the username' };
  }
  if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
    return {
      valid: false,
      message:
        'Password must contain at least one lowercase, \
        one uppercase, and one digit',
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
