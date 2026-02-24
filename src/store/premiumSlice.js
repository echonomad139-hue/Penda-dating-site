import { create } from 'zustand';

const usePremiumStore = create((set) => ({
  isPremium: false,
  tier: 'free', // 'free', 'premium', 'vip'
  plan: null,
  showUpgradeModal: false,

  setPremium: (isPremium, plan = null, tier = 'premium') => 
    set({ isPremium, plan, tier: isPremium ? tier : 'free' }),

  openUpgradeModal: () => set({ showUpgradeModal: true }),
  closeUpgradeModal: () => set({ showUpgradeModal: false }),
}));

export default usePremiumStore;
