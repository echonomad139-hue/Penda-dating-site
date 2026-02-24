import { useState, useCallback, useRef } from 'react';
import { Heart, X, MessageCircle, Mic, MapPin, Shield, Clock } from 'lucide-react';
import Button from '../components/UI/Button';
import { SkeletonCard } from '../components/UI/Skeleton';
import { ICEBREAKER_QUESTIONS } from '../config';
import './DiscoverPage.css';

// Demo profiles for frontend display
const DEMO_PROFILES = [];

export default function DiscoverPage() {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isLoading] = useState(false);
  const [showQuestion, setShowQuestion] = useState(false);
  const [swipeDir, setSwipeDir] = useState(null);
  const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });
  const [isDragging, setIsDragging] = useState(false);
  const startPos = useRef({ x: 0, y: 0 });

  const profiles = DEMO_PROFILES;
  const currentProfile = profiles[currentIndex];

  const [randomQuestions] = useState(() => {
    const shuffled = [...ICEBREAKER_QUESTIONS].sort(() => 0.5 - Math.random());
    return shuffled.slice(0, 3);
  });

  const handleSwipe = useCallback((direction) => {
    setSwipeDir(direction);
    setTimeout(() => {
      setCurrentIndex((i) => Math.min(i + 1, profiles.length - 1));
      setSwipeDir(null);
      setDragOffset({ x: 0, y: 0 });
      setShowQuestion(false);
    }, 300);
  }, [profiles.length]);

  const handleDragStart = (e) => {
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    startPos.current = { x: clientX, y: clientY };
    setIsDragging(true);
  };

  const handleDragMove = (e) => {
    if (!isDragging) return;
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    setDragOffset({
      x: clientX - startPos.current.x,
      y: (clientY - startPos.current.y) * 0.3,
    });
  };

  const handleDragEnd = () => {
    setIsDragging(false);
    if (Math.abs(dragOffset.x) > 100) {
      handleSwipe(dragOffset.x > 0 ? 'right' : 'left');
    } else {
      setDragOffset({ x: 0, y: 0 });
    }
  };

  if (isLoading) {
    return (
      <div className="discover-page">
        <div className="discover-page__header">
          <h2 className="discover-page__title">Discover</h2>
        </div>
        <SkeletonCard />
      </div>
    );
  }

  if (!currentProfile || currentIndex >= profiles.length) {
    return (
      <div className="discover-page">
        <div className="discover-page__header">
          <h2 className="discover-page__title">Discover</h2>
        </div>
        <div className="discover-page__empty">
          <Heart size={48} className="discover-page__empty-icon" />
          <h3>No more profiles</h3>
          <p>Check back later for new connections</p>
        </div>
      </div>
    );
  }

  const cardRotation = dragOffset.x * 0.08;
  const cardStyle = {
    transform: swipeDir
      ? `translateX(${swipeDir === 'right' ? 300 : -300}px) rotate(${swipeDir === 'right' ? 15 : -15}deg)`
      : `translateX(${dragOffset.x}px) translateY(${dragOffset.y}px) rotate(${cardRotation}deg)`,
    transition: isDragging ? 'none' : 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    opacity: swipeDir ? 0 : 1,
  };

  return (
    <div className="discover-page">
      <div className="discover-page__header">
        <h2 className="discover-page__title">Discover</h2>
        <span className="discover-page__count">{profiles.length - currentIndex} left</span>
      </div>

      <div className="discover-page__card-area">
        <div
          className="discover-card"
          style={cardStyle}
          onMouseDown={handleDragStart}
          onMouseMove={handleDragMove}
          onMouseUp={handleDragEnd}
          onMouseLeave={() => isDragging && handleDragEnd()}
          onTouchStart={handleDragStart}
          onTouchMove={handleDragMove}
          onTouchEnd={handleDragEnd}
        >
          {/* Photo */}
          <div className="discover-card__photo">
            <img src={currentProfile.photo} alt={currentProfile.name} draggable="false" />
            <div className="discover-card__gradient" />

            {/* Overlay info */}
            <div className="discover-card__overlay">
              <div className="discover-card__main-info">
                <h3 className="discover-card__name">
                  {currentProfile.name}
                  <span className="discover-card__age">, {currentProfile.age}</span>
                </h3>
                <div className="discover-card__meta">
                  <span className="discover-card__location">
                    <MapPin size={13} /> {currentProfile.country}
                  </span>
                  <span className="discover-card__continent-chip">
                    {currentProfile.continent}
                  </span>
                </div>
              </div>
              {currentProfile.verified && (
                <span className="discover-card__verified">
                  <Shield size={13} /> Verified
                </span>
              )}
            </div>

            {/* Swipe indicators */}
            {dragOffset.x > 40 && (
              <div className="discover-card__indicator discover-card__indicator--like">
                LIKE
              </div>
            )}
            {dragOffset.x < -40 && (
              <div className="discover-card__indicator discover-card__indicator--pass">
                PASS
              </div>
            )}
          </div>

          {/* Below photo section */}
          <div className="discover-card__body">
            {currentProfile.activeRecently && (
              <div className="discover-card__active">
                <span className="discover-card__active-dot" />
                Active recently
              </div>
            )}

            <div className="discover-card__joined">
              <Clock size={13} /> Joined {currentProfile.joinedAgo}
            </div>

            <p className="discover-card__bio">{currentProfile.bio}</p>

            {/* Interests */}
            <div className="discover-card__interests">
              {currentProfile.interests.map((interest) => (
                <span key={interest} className="discover-card__tag">
                  {interest}
                </span>
              ))}
            </div>

            {/* Voice intro button */}
            <button className="discover-card__voice-btn">
              <Mic size={16} /> Voice Intro
            </button>
          </div>
        </div>
      </div>

      {/* Action buttons */}
      <div className="discover-page__actions">
        <button
          className="discover-page__action-btn discover-page__action-btn--pass"
          onClick={() => handleSwipe('left')}
        >
          <X size={26} />
        </button>
        <button
          className="discover-page__action-btn discover-page__action-btn--question"
          onClick={() => setShowQuestion(!showQuestion)}
        >
          <MessageCircle size={22} />
        </button>
        <button
          className="discover-page__action-btn discover-page__action-btn--like"
          onClick={() => handleSwipe('right')}
        >
          <Heart size={26} />
        </button>
      </div>

      {/* Icebreaker questions panel */}
      {showQuestion && (
        <div className="discover-page__questions animate-slide-up">
          <h4 className="discover-page__questions-title">Send a Question</h4>
          <p className="discover-page__questions-sub">Start with curiosity, not a swipe</p>
          {randomQuestions.map((q, i) => (
            <button key={i} className="discover-page__question-btn">
              {q}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
