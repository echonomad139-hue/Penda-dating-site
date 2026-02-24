import { Outlet, useLocation } from 'react-router-dom';
import BottomNav from './BottomNav';
import './AppLayout.css';

export default function AppLayout() {
  const location = useLocation();
  const hideNavRoutes = ['/', '/signup', '/login', '/profile-setup'];
  const showNav = !hideNavRoutes.includes(location.pathname);

  return (
    <div className="app-layout">
      <div className="app-layout__content">
        <Outlet />
      </div>
      {showNav && <BottomNav />}
    </div>
  );
}
