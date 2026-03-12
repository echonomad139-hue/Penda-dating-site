import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import SocketProvider from './context/SocketContext';
import AppLayout from './components/Layout/AppLayout';
import AuthGuard from './components/Layout/AuthGuard';
import SplashPage from './pages/SplashPage';
import LoginPage from './pages/LoginPage';
import SignInPage from './pages/SignInPage';
import SignUpPage from './pages/SignUpPage';
import ProfileSetupPage from './pages/ProfileSetupPage';
import DiscoverPage from './pages/DiscoverPage';
import WababaWamamaPage from './pages/WababaWamamaPage';
import ChatListPage from './pages/ChatListPage';
import ChatRoomPage from './pages/ChatRoomPage';
import PremiumPage from './pages/PremiumPage';
import SettingsPage from './pages/SettingsPage';
import NearbyPage from './pages/NearbyPage';
import EditProfilePage from './pages/EditProfilePage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';

export default function App() {
  return (
    <BrowserRouter>
      <SocketProvider>
        <Toaster
          position="top-center"
          toastOptions={{
            duration: 3000,
            style: {
              fontFamily: 'var(--font-body)',
              fontSize: '0.88rem',
              borderRadius: 'var(--radius-md)',
              background: 'var(--color-charcoal)',
              color: '#fff',
            },
          }}
        />
        <Routes>
          {/* Pre-auth routes (no bottom nav) */}
          <Route path="/" element={<SplashPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/signin" element={<SignInPage />} />
          <Route path="/signup" element={<SignUpPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/profile-setup" element={<ProfileSetupPage />} />

          {/* Protected app routes (with bottom nav) */}
          <Route element={<AuthGuard><AppLayout /></AuthGuard>}>
            <Route path="/discover" element={<DiscoverPage />} />
            <Route path="/nearby" element={<NearbyPage />} />
            <Route path="/matches" element={<WababaWamamaPage />} />
            <Route path="/chats" element={<ChatListPage />} />
            <Route path="/chat/:id" element={<ChatRoomPage />} />
            <Route path="/premium" element={<PremiumPage />} />
            <Route path="/settings" element={<SettingsPage />} />
            <Route path="/edit-profile" element={<EditProfilePage />} />
          </Route>
        </Routes>
      </SocketProvider>
    </BrowserRouter>
  );
}
