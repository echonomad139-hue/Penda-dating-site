import { create } from 'zustand';

const useMatchStore = create((set) => ({
  profiles: [],
  currentIndex: 0,
  matches: [],
  isLoading: false,

  setProfiles: (profiles) => set({ profiles, currentIndex: 0 }),

  nextProfile: () => set((state) => ({
    currentIndex: Math.min(state.currentIndex + 1, state.profiles.length - 1),
  })),

  addMatch: (match) => set((state) => ({
    matches: [...state.matches, match],
  })),

  setLoading: (isLoading) => set({ isLoading }),
}));

export default useMatchStore;
