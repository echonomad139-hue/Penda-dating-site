import { Heart, MessageCircle, Sparkles } from 'lucide-react';
import './MatchModal.css';

export default function MatchModal({ profile, userPhoto, onMessage, onKeepSwiping }) {
  if (!profile) return null;

  return (
    <div className="match-modal" onClick={onKeepSwiping}>
      <div className="match-modal__content animate-slide-up" onClick={(e) => e.stopPropagation()}>
        {/* Celebration header */}
        <div className="match-modal__celebration">
          <div className="match-modal__sparkles">
            <Sparkles size={28} />
          </div>
          <h2 className="match-modal__title">It&apos;s a Match!</h2>
          <p className="match-modal__subtitle">
            You and <strong>{profile.name}</strong> liked each other
          </p>
        </div>

        {/* Avatars */}
        <div className="match-modal__avatars">
          <div className="match-modal__avatar-ring match-modal__avatar-ring--left">
            <img
              src={userPhoto || '/default-avatar.png'}
              alt="You"
              className="match-modal__avatar-img"
            />
          </div>
          <div className="match-modal__heart-bridge">
            <Heart size={28} fill="var(--color-terracotta)" color="var(--color-terracotta)" />
          </div>
          <div className="match-modal__avatar-ring match-modal__avatar-ring--right">
            <img
              src={profile.photo || '/default-avatar.png'}
              alt={profile.name}
              className="match-modal__avatar-img"
            />
          </div>
        </div>

        {/* Actions */}
        <div className="match-modal__actions">
          <button className="match-modal__btn match-modal__btn--message" onClick={onMessage}>
            <MessageCircle size={18} />
            Send a Message
          </button>
          <button className="match-modal__btn match-modal__btn--keep" onClick={onKeepSwiping}>
            Keep Swiping
          </button>
        </div>
      </div>
    </div>
  );
}
