const API_BASE_URL = `${location.protocol}//${location.host}/api/`;

export const ENDPOINTS = {
  SIGNUP: `${API_BASE_URL}signup`,
  LOGIN: `${API_BASE_URL}login`,
}

export const HEADER = '{ \'Content-Type\': \'application/json\' }';

// export async function apiRequest(method, endpoint, data = null, isFileUpload = false, needToken = true) {
//   const url = `${endpoint}`;
//   const csrfToken = getCSRFTokenfromCookies();
//   const options = {
//     method,
//     headers: {
//       ...(csrfToken ? { 'X-CSRFToken': csrfToken } : {}),
//       ...(isFileUpload ? {} : { 'Content-Type': 'application/json' }),
//     },
//     credentials: 'include',
//   };

//   if (data) {
//     if (isFileUpload) {
//       options.body = data;
//     } else {
//       options.body = JSON.stringify(data);
//     }
//   }
//   log.info('Sending API request:', method, url);

//   try {
//     const response = await fetch(url, options);

// export function getCSRFTokenfromCookies() {
//   const name = 'csrftoken';
//   let token = null;
//   if (document.cookie && document.cookie !== '') {
//     const cookies = document.cookie.split(';');
//     for (let i = 0; i < cookies.length; i++) {
//       const cookie = cookies[i].trim();
//       if (cookie.startsWith(name)) {
//         token = decodeURIComponent(cookie.substring(name.length + 1));
//         break;
//       }
//     }
//   }
//   return token;
// }