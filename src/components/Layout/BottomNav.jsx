import { NavLink } from 'react-router-dom';
import { Compass, MessageCircle, Heart, User, Crown, MapPin } from 'lucide-react';
import './BottomNav.css';

const navItems = [
  { to: '/discover', icon: Compass, label: 'Discover' },
  { to: '/nearby', icon: MapPin, label: 'Nearby' },
  { to: '/matches', icon: Heart, label: 'Matches' },
  { to: '/chats', icon: MessageCircle, label: 'Chats' },
  { to: '/premium', icon: Crown, label: 'Premium' },
  { to: '/settings', icon: User, label: 'Profile' },
];

export default function BottomNav() {
  return (
    <nav className="bottom-nav">
      {navItems.map(({ to, icon: Icon, label }) => (
        <NavLink
          key={to}
          to={to}
          className={({ isActive }) =>
            `bottom-nav__item ${isActive ? 'bottom-nav__item--active' : ''}`
          }
        >
          <Icon size={22} strokeWidth={1.8} />
          <span className="bottom-nav__label">{label}</span>
        </NavLink>
      ))}
    </nav>
  );
}
