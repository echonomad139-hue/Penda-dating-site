import { Outlet, useLocation } from 'react-router-dom';
import BottomNav from './BottomNav';
import SideNav from './SideNav';
import './AppLayout.css';

export default function AppLayout() {
  const location = useLocation();
  const hideNavRoutes = ['/', '/signup', '/login', '/profile-setup'];
  const showNav = !hideNavRoutes.includes(location.pathname);

  return (
    <div className="app-layout">
      {/* Side nav visible on larger screens */}
      {showNav && <SideNav />}
      <div className="app-layout__content">
        <Outlet />
      </div>
      {/* Bottom nav visible on mobile */}
      {showNav && <BottomNav />}
    </div>
  );
}
