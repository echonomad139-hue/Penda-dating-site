import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../store/authSlice';
import './SplashPage.css';

export default function SplashPage() {
  const navigate = useNavigate();
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const [animating, setAnimating] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setAnimating(false);
      if (isAuthenticated) {
        navigate('/discover');
      } else {
        navigate('/login');
      }
    }, 2800);
    return () => clearTimeout(timer);
  }, [isAuthenticated, navigate]);

  return (
    <div className="splash">
      <div className="splash__gradient" />
      <div className={`splash__content ${animating ? 'splash__content--visible' : ''}`}>
        <div className="splash__logo-ring">
          <span className="splash__logo-letter">P</span>
        </div>
        <h1 className="splash__title">PENDA</h1>
        <p className="splash__tagline">Real connection across continents</p>
        <div className="splash__dots">
          <span className="splash__dot" />
          <span className="splash__dot" />
          <span className="splash__dot" />
        </div>
      </div>
    </div>
  );
}
