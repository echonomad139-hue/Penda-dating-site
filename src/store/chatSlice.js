import { create } from 'zustand';

const useChatStore = create((set) => ({
  conversations: [],
  activeChat: null,
  messages: [],
  isTyping: false,
  onlineUsers: new Set(),

  setConversations: (conversations) => set({ conversations }),

  setActiveChat: (chat) => set({ activeChat: chat, messages: [] }),

  addMessage: (message) => set((state) => ({
    messages: [...state.messages, message],
  })),

  setMessages: (messages) => set({ messages }),

  setTyping: (isTyping) => set({ isTyping }),

  setOnlineUsers: (users) => set({ onlineUsers: new Set(users) }),

  addOnlineUser: (userId) => set((state) => {
    const updated = new Set(state.onlineUsers);
    updated.add(userId);
    return { onlineUsers: updated };
  }),

  removeOnlineUser: (userId) => set((state) => {
    const updated = new Set(state.onlineUsers);
    updated.delete(userId);
    return { onlineUsers: updated };
  }),
}));

export default useChatStore;
