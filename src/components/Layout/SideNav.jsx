import { NavLink } from 'react-router-dom';
import { Compass, MessageCircle, Heart, User, Crown, MapPin } from 'lucide-react';
import './SideNav.css';

const navItems = [
  { to: '/discover', icon: Compass, label: 'Discover' },
  { to: '/nearby', icon: MapPin, label: 'Nearby' },
  { to: '/matches', icon: Heart, label: 'Matches' },
  { to: '/chats', icon: MessageCircle, label: 'Chats' },
  { to: '/premium', icon: Crown, label: 'Premium' },
  { to: '/settings', icon: User, label: 'Profile' },
];

export default function SideNav() {
  return (
    <aside className="side-nav">
      <div className="side-nav__brand">
        <span className="side-nav__logo">P</span>
        <span className="side-nav__brand-name">PENDA</span>
      </div>
      <nav className="side-nav__menu">
        {navItems.map(({ to, icon: Icon, label }) => (
          <NavLink
            key={to}
            to={to}
            className={({ isActive }) =>
              `side-nav__item ${isActive ? 'side-nav__item--active' : ''}`
            }
          >
            <Icon size={20} strokeWidth={1.8} />
            <span className="side-nav__label">{label}</span>
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
