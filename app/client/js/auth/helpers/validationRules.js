import { api, endpoints } from '../../api';

let rules;

try {
  const response = await api.get(endpoints.VALIDATION_RULES);
  rules = response.data;
} catch (error) {
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
