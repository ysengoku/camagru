import { api, ENDPOINTS } from '../api';

let rules;

try {
  rules = await api.get(ENDPOINTS.VALIDATION_RULES);
} catch (error) {
  console.error('Failed to fetch validation rules:', error);
  // Fallback to default rules if fetching fails
  rules = {
    username: {
      minLength: 3,
      maxLength: 20,
      pattern: '^[a-zA-Z0-9_-]+$',
    },
    email: {
      maxLength: 254,
      pattern: '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
    },
    password: {
      minLength: 14,
      maxLength: 72,
      requireLower: true,
      requireUpper: true,
      requireDigit: true,
      requireSpecial: true,
    },
  };
}

export { rules };
