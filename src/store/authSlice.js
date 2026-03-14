import { create } from 'zustand';

const useAuthStore = create((set) => ({
  user: null,
  token: localStorage.getItem('penda_token') || null,
  isAuthenticated: !!localStorage.getItem('penda_token'),
  isLoading: false,
  error: null,
  pendingRegistration: null,

  setUser: (user) => set({ user, isAuthenticated: true }),

  setToken: (token) => {
    localStorage.setItem('penda_token', token);
    set({ token, isAuthenticated: true });
  },

  login: async () => {
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

  updateProfile: async (profileData) => {
    set({ isLoading: true, error: null });
    try {
      // API call will be wired here
      set((state) => {
        const updatedUser = { ...state.user, ...profileData };
        return { user: updatedUser, isLoading: false };
      });
    } catch (error) {
      set({ error: error.message, isLoading: false });
    }
  },

  logout: () => {
    localStorage.removeItem('penda_token');
    set({ user: null, token: null, isAuthenticated: false, pendingRegistration: null });
  },

  setPendingRegistration: (data) => set({ pendingRegistration: data }),

  clearPendingRegistration: () => set({ pendingRegistration: null }),

  requestRegistrationOTP: async (email, deliveryMethod = 'email') => {
    set({ isLoading: true, error: null });
    try {
      console.debug('Mock request registration OTP for:', email, 'via', deliveryMethod);
      await new Promise(resolve => setTimeout(resolve, 1000));
      set({ isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  verifyRegistrationOTP: async (email, otp) => {
    set({ isLoading: true, error: null });
    try {
      console.debug('Mock verify registration OTP for:', email, otp);
      await new Promise(resolve => setTimeout(resolve, 1000));
      set({ isLoading: false });
      return true;
    } catch (error) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  requestOTP: async (email, deliveryMethod = 'email') => {
    set({ isLoading: true, error: null });
    try {
      console.debug('Mock request OTP for:', email, 'via', deliveryMethod);
      await new Promise(resolve => setTimeout(resolve, 1000));
      set({ isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  verifyOTP: async (email, otp) => {
    set({ isLoading: true, error: null });
    try {
      console.debug('Mock verify OTP for:', email, otp);
      await new Promise(resolve => setTimeout(resolve, 1000));
      set({ isLoading: false });
      return true;
    } catch (error) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  resetPassword: async (email, otp, newPassword) => {
    set({ isLoading: true, error: null });
    try {
      console.debug('Mock reset password for:', email, otp, newPassword);
      await new Promise(resolve => setTimeout(resolve, 1000));
      set({ isLoading: false });
    } catch (error) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  clearError: () => set({ error: null }),
}));

export default useAuthStore;
