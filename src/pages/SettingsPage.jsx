import { useState, useEffect } from 'react';
import { ChevronRight, User, Globe, Bell, Shield, LogOut, Moon, HelpCircle, X } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../store/authSlice';
import './SettingsPage.css';

export default function SettingsPage() {
  const navigate = useNavigate();
  const { user, logout } = useAuthStore();
  
  // Local states for toggles and modals
  const [isDark, setIsDark] = useState(false);
  const [notifications, setNotifications] = useState(true);
  const [activeModal, setActiveModal] = useState(null); // 'language', 'privacy', 'support', null
  const [language, setLanguage] = useState('English');
  const [incognito, setIncognito] = useState(false);

  // Apply dark mode class to body whenever it changes
  useEffect(() => {
    if (isDark) {
      document.body.classList.add('dark-mode');
    } else {
      document.body.classList.remove('dark-mode');
    }
  }, [isDark]);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const menuItems = [
    { icon: User, label: 'Edit Profile', onClick: () => navigate('/profile-setup') },
    { icon: Globe, label: `Language (${language})`, onClick: () => setActiveModal('language') },
    { icon: Bell, label: 'Notifications', toggle: notifications, onToggle: () => setNotifications(!notifications) },
    { icon: Shield, label: 'Privacy & Safety', onClick: () => setActiveModal('privacy') },
    { icon: Moon, label: 'Dark Mode', toggle: isDark, onToggle: () => setIsDark(!isDark) },
    { icon: HelpCircle, label: 'Help & Support', onClick: () => setActiveModal('support') },
  ];

  return (
    <div className="settings-page">
      {/* Profile card */}
      <div className="settings-page__profile-card">
        <div className="settings-page__avatar-placeholder">
          <User size={28} />
        </div>
        <div className="settings-page__profile-info">
          <h3 className="settings-page__name">{user?.name || 'Your Name'}</h3>
          <p className="settings-page__meta">
            {user?.country || 'No location set'} · Joined recently
          </p>
          <p className="settings-page__views">3 people viewed your profile today</p>
        </div>
      </div>

      {/* Menu */}
      <div className="settings-page__menu">
        {menuItems.map(({ icon: Icon, label, onClick, toggle, onToggle }) => (
          <button
            key={label}
            className="settings-page__menu-item"
            onClick={onClick || onToggle}
          >
            <div className="settings-page__menu-left">
              <Icon size={20} />
              <span>{label}</span>
            </div>
            {toggle !== undefined ? (
              <div className={`settings-page__toggle ${toggle ? 'settings-page__toggle--active' : ''}`}>
                <div className="settings-page__toggle-thumb" />
              </div>
            ) : (
              <ChevronRight size={18} className="settings-page__chevron" />
            )}
          </button>
        ))}
      </div>

      <button className="settings-page__logout" onClick={handleLogout}>
        <LogOut size={18} />
        <span>Sign Out</span>
      </button>

      <p className="settings-page__version">PENDA v1.0.0</p>

      {/* Modals */}
      {activeModal && (
        <div className="settings-modal" onClick={() => setActiveModal(null)}>
          <div className="settings-modal__content" onClick={(e) => e.stopPropagation()}>
            <button className="settings-modal__close" onClick={() => setActiveModal(null)}>
              <X size={20} />
            </button>

            {activeModal === 'language' && (
              <div className="settings-modal__pane">
                <h3>Select Language</h3>
                <div className="settings-modal__options">
                  {['English', 'Français', 'Swahili'].map((lang) => (
                    <button 
                      key={lang} 
                      className={`settings-modal__option ${language === lang ? 'active' : ''}`}
                      onClick={() => { setLanguage(lang); setActiveModal(null); }}
                    >
                      {lang}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {activeModal === 'privacy' && (
              <div className="settings-modal__pane">
                <h3>Privacy & Safety</h3>
                <div className="settings-modal__setting">
                  <div>
                    <h4>Incognito Mode</h4>
                    <p>Hide your profile from discovery</p>
                  </div>
                  <button 
                    className={`settings-page__toggle ${incognito ? 'settings-page__toggle--active' : ''}`}
                    onClick={() => setIncognito(!incognito)}
                  >
                    <div className="settings-page__toggle-thumb" />
                  </button>
                </div>
                <div className="settings-modal__actions">
                  <button className="settings-modal__btn-outline">Blocked Contacts</button>
                  <button className="settings-modal__btn-danger">Delete Account</button>
                </div>
              </div>
            )}

            {activeModal === 'support' && (
              <div className="settings-modal__pane">
                <h3>Help & Support</h3>
                <p className="settings-modal__text">
                  Need assistance? We're here to help you on your journey.
                </p>
                <div className="settings-modal__actions">
                  <button className="settings-modal__btn-primary">Contact Support</button>
                  <button className="settings-modal__btn-outline">Read FAQs</button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
