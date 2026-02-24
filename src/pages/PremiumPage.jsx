import { Crown, Zap, Shield, Eye, Heart, Star } from 'lucide-react';
import Button from '../components/UI/Button';
import usePremiumStore from '../store/premiumSlice';
import './PremiumPage.css';

const FEATURES = [
  { icon: Eye, text: 'See who viewed your profile', isVip: false },
  { icon: Heart, text: 'Unlimited likes & matches', isVip: false },
  { icon: Zap, text: 'Priority in Discover feed', isVip: false },
  { icon: Shield, text: 'Access WABABA & WAMAMA', isVip: true },
];

const PREMIUM_PLANS = [
  { id: 'monthly', label: '1 Month', price: '$9.99', period: '/month', popular: false },
  { id: 'quarterly', label: '3 Months', price: '$7.99', period: '/month', popular: true, save: '20%' },
  { id: 'yearly', label: '12 Months', price: '$4.99', period: '/month', popular: false, save: '50%' },
];

const VIP_PLANS = [
  { id: 'vip_monthly', label: '1 Mo VIP', price: '$19.99', period: '/month', popular: false },
  { id: 'vip_quarterly', label: '3 Mo VIP', price: '$14.99', period: '/month', popular: true, save: '25%' },
];

export default function PremiumPage() {
  const { isPremium, tier, setPremium } = usePremiumStore();

  if (tier === 'vip') {
    return (
      <div className="premium-page premium-page--active">
        <div className="premium-page__active-badge">
          <Crown size={32} className="premium-page__crown" />
          <h2>You're VIP</h2>
          <p>All features & WABABA/WAMAMA unlocked</p>
        </div>
      </div>
    );
  }

  return (
    <div className="premium-page">
      <div className="premium-page__hero">
        <div className="premium-page__hero-bg" />
        <div className="premium-page__hero-content">
          <Crown size={36} className="premium-page__crown" />
          <h1 className="premium-page__heading">PENDA {isPremium ? 'VIP' : 'Premium'}</h1>
          <p className="premium-page__tagline">Elevate your experience</p>
        </div>
      </div>

      <div className="premium-page__body">
        <div className="premium-page__features">
          {FEATURES.map((feature, i) => {
            const IconComponent = feature.icon;
            return (
              <div key={i} className="premium-page__feature">
                <div className={`premium-page__feature-icon ${feature.isVip ? 'premium-page__feature-icon--vip' : ''}`}>
                  <IconComponent size={18} />
                </div>
                <span style={{ color: feature.isVip ? 'var(--color-gold)' : 'inherit' }}>
                  {feature.text} {feature.isVip && <Star size={12} style={{ display: 'inline', marginLeft: 4 }}/>}
                </span>
              </div>
            );
          })}
        </div>

        {!isPremium && (
          <>
            <h3 className="premium-page__section-title">Premium Plans</h3>
            <div className="premium-page__plans">
              {PREMIUM_PLANS.map((plan) => (
                <button
                  key={plan.id}
                  className={`premium-page__plan ${plan.popular ? 'premium-page__plan--popular' : ''}`}
                  onClick={() => setPremium(true, plan.id, 'premium')}
                >
                  {plan.popular && <span className="premium-page__plan-badge">Most Popular</span>}
                  <span className="premium-page__plan-label">{plan.label}</span>
                  <span className="premium-page__plan-price">{plan.price}</span>
                  <span className="premium-page__plan-period">{plan.period}</span>
                  {plan.save && <span className="premium-page__plan-save">Save {plan.save}</span>}
                </button>
              ))}
            </div>
          </>
        )}

        <h3 className="premium-page__section-title">
          <Star size={16} /> VIP Plans (Includes Wababa/Wamama)
        </h3>
        <div className="premium-page__plans">
          {VIP_PLANS.map((plan) => (
            <button
              key={plan.id}
              className={`premium-page__plan premium-page__plan--vip ${plan.popular ? 'premium-page__plan--popular' : ''}`}
              onClick={() => setPremium(true, plan.id, 'vip')}
            >
              {plan.popular && <span className="premium-page__plan-badge">Most Popular</span>}
              <span className="premium-page__plan-label">{plan.label}</span>
              <span className="premium-page__plan-price">{plan.price}</span>
              <span className="premium-page__plan-period">{plan.period}</span>
              {plan.save && <span className="premium-page__plan-save">Save {plan.save}</span>}
            </button>
          ))}
        </div>

        <p className="premium-page__disclaimer">
          Cancel anytime. Billed per period. By subscribing you agree to our terms.
        </p>
      </div>
    </div>
  );
}
