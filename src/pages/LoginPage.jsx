import { useNavigate } from 'react-router-dom';
import { Heart } from 'lucide-react';
import './LoginPage.css';

export default function LoginPage() {
  const navigate = useNavigate();

  return (
    <div className="landing">
      <div className="landing__bg" />

      <div className="landing__content">
        <div className="landing__logo-ring">
          <Heart size={32} fill="#fff" strokeWidth={0} />
        </div>

        <h1 className="landing__title">PENDA</h1>
        <p className="landing__tagline">Love Without Borders</p>
        <p className="landing__desc">
          Connect with beautiful souls across Africa and the diaspora
        </p>

        <div className="landing__actions">
          <button
            className="landing__btn landing__btn--primary"
            onClick={() => navigate('/signup')}
          >
            Get Started
          </button>
          <button
            className="landing__btn landing__btn--outline"
            onClick={() => navigate('/signin')}
          >
            I already have an account
          </button>
        </div>

        <p className="landing__terms">
          By continuing, you agree to our{' '}
          <a href="#terms">Terms</a> & <a href="#privacy">Privacy Policy</a>
        </p>
      </div>
    </div>
  );
}
