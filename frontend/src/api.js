const API_TOKEN = import.meta.env.VITE_API_TOKEN;

async function apiRequest(path, options = {}) {
  const response = await fetch(path, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-AUTH-TOKEN': API_TOKEN,
      ...(options.headers || {}),
    },
  });

  if (!response.ok) {
    const text = await response.text();
    throw new Error(text || 'Request failed');
  }

  if (response.status === 204) {
    return null;
  }

  return response.json();
}

export const api = {
  listUsers: () => apiRequest('/api/users'),
  createUser: (data) =>
    apiRequest('/api/users', {
      method: 'POST',
      body: JSON.stringify(data),
    }),
  deleteUser: (id) => apiRequest(`/api/users/${id}`, { method: 'DELETE' }),

  listArticles: () => apiRequest('/api/articles'),
  createArticle: (data) =>
    apiRequest('/api/articles', {
      method: 'POST',
      body: JSON.stringify(data),
    }),
  deleteArticle: (id) => apiRequest(`/api/articles/${id}`, { method: 'DELETE' }),
};
