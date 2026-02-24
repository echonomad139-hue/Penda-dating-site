import { Lock, Crown, Star, Sparkles } from 'lucide-react';
import usePremiumStore from '../store/premiumSlice';
import Button from '../components/UI/Button';
import './WababaWamamaPage.css';

const DEMO_SPECIALS = [
  {
    id: 1, name: 'Grace', age: 45, type: 'wamama', country: 'Nigeria',
    bio: 'Life taught me patience. Now I seek genuine companionship.',
    photo: 'https://images.unsplash.com/photo-1589156280159-27698a70f29e?w=400&h=500&fit=crop',
  },
  {
    id: 2, name: 'Joseph', age: 52, type: 'wababa', country: 'Tanzania',
    bio: 'Retired pilot. Still exploring, but this time looking for a co-pilot for life.',
    photo: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=500&fit=crop',
  },
  {
    id: 3, name: 'Margaret', age: 48, type: 'wamama', country: 'Kenya',
    bio: 'Teacher, mother, dreamer. Still believing in butterflies.',
    photo: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=500&fit=crop',
  },
];

export default function WababaWamamaPage() {
  const { tier, openUpgradeModal } = usePremiumStore();
  const hasVipAccess = tier === 'vip';

  return (
    <div className="wababa-page">
      <div className="wababa-page__header">
        <h2 className="wababa-page__title">
          <Sparkles size={20} /> WABABA & WAMAMA
        </h2>
        <p className="wababa-page__subtitle">Mature connections. Richer conversations.</p>
      </div>

      <div className="wababa-page__grid">
        {DEMO_SPECIALS.map((profile) => (
          <div key={profile.id} className="wababa-card">
            <div className="wababa-card__photo-wrapper">
              <img
                src={profile.photo}
                alt={profile.name}
                className={`wababa-card__photo ${!hasVipAccess ? 'wababa-card__photo--blurred' : ''}`}
              />
              {!hasVipAccess && (
                <div className="wababa-card__lock-overlay">
                  <div className="wababa-card__lock-icon">
                    <Lock size={20} />
                  </div>
                  <div className="wababa-card__shimmer" />
                </div>
              )}
            </div>
            <div className="wababa-card__info">
              <h4 className="wababa-card__name">
                {hasVipAccess ? profile.name : '••••••'}, {profile.age}
              </h4>
              <span className="wababa-card__type-badge">
                {profile.type === 'wababa' ? '👨 Wababa' : '👩 Wamama'}
              </span>
              <p className="wababa-card__bio">
                {hasVipAccess ? profile.bio : 'Upgrade to VIP to see this profile...'}
              </p>
            </div>
          </div>
        ))}
      </div>

      {!hasVipAccess && (
        <div className="wababa-page__upgrade animate-slide-up">
          <div className="wababa-page__upgrade-content">
            <Crown size={28} className="wababa-page__upgrade-icon" />
            <h3>Unlock WABABA & WAMAMA</h3>
            <p>Access mature, verified profiles by upgrading to VIP</p>
            <Button variant="gold" fullWidth onClick={openUpgradeModal}>
              Upgrade to VIP
            </Button>
          </div>
        </div>
      )}
    </div>
  );
}
