import { useState } from 'react';
import { X, MapPin, Shield, Clock, Heart, MessageCircle, ChevronLeft, ChevronRight } from 'lucide-react';
import './FullProfileModal.css';

export default function FullProfileModal({ profile, onClose, onLike, onMessage }) {
  const [photoIndex, setPhotoIndex] = useState(0);

  if (!profile) return null;

  // Support multiple photos or fallback to single
  const photos = profile.photos || [profile.photo];

  const nextPhoto = () => setPhotoIndex((i) => Math.min(i + 1, photos.length - 1));
  const prevPhoto = () => setPhotoIndex((i) => Math.max(i - 1, 0));

  return (
    <div className="full-profile" onClick={onClose}>
      <div className="full-profile__content animate-slide-up" onClick={(e) => e.stopPropagation()}>
        {/* Close button */}
        <button className="full-profile__close" onClick={onClose}>
          <X size={22} />
        </button>

        {/* Photo carousel */}
        <div className="full-profile__photo-area">
          <img
            src={photos[photoIndex]}
            alt={`${profile.name} photo ${photoIndex + 1}`}
            className="full-profile__photo"
          />
          <div className="full-profile__photo-gradient" />

          {/* Photo pagination dots */}
          {photos.length > 1 && (
            <>
              <div className="full-profile__dots">
                {photos.map((_, i) => (
                  <span
                    key={i}
                    className={`full-profile__dot ${i === photoIndex ? 'full-profile__dot--active' : ''}`}
                  />
                ))}
              </div>
              {photoIndex > 0 && (
                <button className="full-profile__nav full-profile__nav--prev" onClick={prevPhoto}>
                  <ChevronLeft size={20} />
                </button>
              )}
              {photoIndex < photos.length - 1 && (
                <button className="full-profile__nav full-profile__nav--next" onClick={nextPhoto}>
                  <ChevronRight size={20} />
                </button>
              )}
            </>
          )}

          {/* Overlay name */}
          <div className="full-profile__name-overlay">
            <h2>
              {profile.name}
              {profile.age && <span>, {profile.age}</span>}
            </h2>
            {profile.verified && (
              <span className="full-profile__verified">
                <Shield size={14} /> Verified
              </span>
            )}
          </div>
        </div>

        {/* Details */}
        <div className="full-profile__details">
          {/* Location */}
          {profile.country && (
            <div className="full-profile__meta-row">
              <MapPin size={15} />
              <span>{profile.country}</span>
              {profile.continent && (
                <span className="full-profile__continent-tag">{profile.continent}</span>
              )}
            </div>
          )}

          {profile.joinedAgo && (
            <div className="full-profile__meta-row">
              <Clock size={15} />
              <span>Joined {profile.joinedAgo}</span>
            </div>
          )}

          {/* Bio */}
          {profile.bio && (
            <div className="full-profile__section">
              <h4>About</h4>
              <p>{profile.bio}</p>
            </div>
          )}

          {/* Interests */}
          {profile.interests && profile.interests.length > 0 && (
            <div className="full-profile__section">
              <h4>Interests</h4>
              <div className="full-profile__tags">
                {profile.interests.map((interest) => (
                  <span key={interest} className="full-profile__tag">{interest}</span>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Action buttons */}
        <div className="full-profile__actions">
          <button className="full-profile__action-btn full-profile__action-btn--like" onClick={onLike}>
            <Heart size={20} /> Like
          </button>
          <button className="full-profile__action-btn full-profile__action-btn--message" onClick={onMessage}>
            <MessageCircle size={20} /> Message
          </button>
        </div>
      </div>
    </div>
  );
}
