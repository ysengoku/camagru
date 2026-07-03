const API_BASE_URL = `${location.protocol}//${location.host}/api/`;

/** @type {Record<string, string>} */
export const endpoints = {
  SIGNUP: `${API_BASE_URL}signup`,
  LOGIN: `${API_BASE_URL}login`,
  LOGOUT: `${API_BASE_URL}logout`,
  VERIFY_EMAIL: `${API_BASE_URL}verify-email`,
  PASSWORD_RESET_REQUEST: `${API_BASE_URL}forgot-password`,
  RESET_PASSWORD: `${API_BASE_URL}reset-password`,
  RESEND_EMAIL: `${API_BASE_URL}resend-email`,
  VALIDATION_RULES: `${API_BASE_URL}validation-rules`,
  PHOTOS: `${API_BASE_URL}photos`,
  STUDIO_CONFIG: `${API_BASE_URL}studio-config`,
};

/**
 * @typedef {Object} ApiError
 * @property {number} status - HTTP status code
 * @property {unknown} data  - Parsed response body
 */

/**
 * @typedef {Object} ApiClient
 * @property {(path: string) => Promise<unknown>}              get
 * @property {(path: string, body: unknown) => Promise<unknown>} post
 * @property {(path: string, body: unknown) => Promise<unknown>} put
 * @property {(path: string) => Promise<unknown>}              delete
 */

/**
 * Creates an API client bound to the given base URL.
 * All methods resolve with the parsed JSON body on 2xx Status codes,
 * or throw an ApiError on non-2xx or network failure.
 *
 * @param {string} baseURL
 * @returns {ApiClient}
 */
function createApiClient() {
  /**
   * @param {string}  method
   * @param {string}  url
   * @param {unknown} [body]
   * @returns {Promise<unknown>}
   * @throws {ApiError}
   */
  async function request(method, url, body = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const options = { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken } };
    if (body) {
      options.body = JSON.stringify(body);
    }

    const res = await fetch(url, options);
    const data = await res.json();
    if (!res.ok) {
      throw { status: res.status, data: data };
    }
    return { ok: res.ok, data: data };
  }

  return {
    get: (path) => request('GET', path),
    post: (path, body) => request('POST', path, body),
    put: (path, body) => request('PUT', path, body),
    delete: (path) => request('DELETE', path),
  };
}

export const api = createApiClient(API_BASE_URL);
