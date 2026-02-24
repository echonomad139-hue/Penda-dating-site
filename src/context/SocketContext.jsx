import { createContext, useContext, useEffect, useState } from 'react';
import { io } from 'socket.io-client';
import useAuthStore from '../store/authSlice';

const SocketContext = createContext(null);

export function useSocket() {
  return useContext(SocketContext);
}

export default function SocketProvider({ children }) {
  const [socket, setSocket] = useState(null);
  const token = useAuthStore((s) => s.token);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);

  useEffect(() => {
    if (!isAuthenticated || !token) {
      // Disconnect any existing socket when not authenticated
      if (socket) {
        socket.disconnect();
        setSocket(null);
      }
      return;
    }

    const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';
    const newSocket = io(API_URL, {
      auth: { token },
      transports: ['websocket', 'polling'],
      reconnectionAttempts: 5,
      reconnectionDelay: 2000,
    });

    newSocket.on('connect', () => {
      console.log('[Socket] Connected:', newSocket.id);
    });

    newSocket.on('connect_error', (err) => {
      console.warn('[Socket] Connection error:', err.message);
    });

    newSocket.on('disconnect', (reason) => {
      console.log('[Socket] Disconnected:', reason);
    });

    setSocket(newSocket);

    return () => {
      newSocket.disconnect();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAuthenticated, token]);

  return (
    <SocketContext.Provider value={socket}>
      {children}
    </SocketContext.Provider>
  );
}
