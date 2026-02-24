import { create } from 'zustand';

const useAuthStore = create((set) => ({
  user: null,
  token: localStorage.getItem('penda_token') || null,
  isAuthenticated: !!localStorage.getItem('penda_token'),
  isLoading: false,
  error: null,

  setUser: (user) => set({ user, isAuthenticated: true }),

  setToken: (token) => {
    localStorage.setItem('penda_token', token);
    set({ token, isAuthenticated: true });
  },

  login: async (credentials) => {
    set({ isLoading: true, error: null });
    try {
      // API call will be wired here
      const response = { token: 'mock_token', user: { name: 'Test User' } };
      localStorage.setItem('penda_token', response.token);
      set({ user: response.user, token: response.token, isAuthenticated: true, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  register: async (userData) => {
    set({ isLoading: true, error: null });
    try {
      // API call will be wired here
      const response = { token: 'mock_token', user: userData };
      localStorage.setItem('penda_token', response.token);
      set({ user: response.user, token: response.token, isAuthenticated: true, isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  logout: () => {
    localStorage.removeItem('penda_token');
    set({ user: null, token: null, isAuthenticated: false });
  },

  clearError: () => set({ error: null }),
}));

export default useAuthStore;
