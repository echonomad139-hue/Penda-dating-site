import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Camera, X, Plus, Check } from 'lucide-react';
import useAuthStore from '../store/authSlice';
import { ICEBREAKER_QUESTIONS } from '../config';
import './EditProfilePage.css';

const INTEREST_OPTIONS = [
  'Travel', 'Music', 'Photography', 'Art', 'Cooking',
  'Fitness', 'Reading', 'Coffee', 'Dancing', 'Nature',
  'Movies', 'Tech', 'Fashion', 'Football', 'Gaming',
];

export default function EditProfilePage() {
  const navigate = useNavigate();
  const { user } = useAuthStore();

  const [photos, setPhotos] = useState(user?.photos || [null, null, null, null, null, null]);
  const [displayName, setDisplayName] = useState(user?.name || '');
  const [bio, setBio] = useState(user?.bio || '');
  const [selectedInterests, setSelectedInterests] = useState(user?.interests || []);
  const [saving, setSaving] = useState(false);

  const handlePhotoUpload = useCallback((index) => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = () => {
          const newPhotos = [...photos];
          newPhotos[index] = reader.result;
          setPhotos(newPhotos);
        };
        reader.readAsDataURL(file);
      }
    };
    input.click();
  }, [photos]);

  const removePhoto = (index) => {
    const newPhotos = [...photos];
    newPhotos[index] = null;
    setPhotos(newPhotos);
  };

  const toggleInterest = (interest) => {
    setSelectedInterests((prev) =>
      prev.includes(interest)
        ? prev.filter((i) => i !== interest)
        : prev.length < 8 ? [...prev, interest] : prev
    );
  };

  const handleSave = async () => {
    setSaving(true);
    // TODO: Wire up API call
    setTimeout(() => {
      setSaving(false);
      navigate('/settings');
    }, 800);
  };

  return (
    <div className="edit-profile">
      {/* Header */}
      <div className="edit-profile__header">
        <button className="edit-profile__back" onClick={() => navigate(-1)}>
          <ArrowLeft size={22} />
        </button>
        <h2 className="edit-profile__title">Edit Profile</h2>
        <button
          className="edit-profile__save"
          onClick={handleSave}
          disabled={saving}
        >
          {saving ? '...' : 'Save'}
        </button>
      </div>

      {/* Photo grid */}
      <div className="edit-profile__section">
        <h3 className="edit-profile__section-title">Photos</h3>
        <p className="edit-profile__section-hint">Add up to 6 photos. The first one is your main photo.</p>
        <div className="edit-profile__photo-grid">
          {photos.map((photo, index) => (
            <div
              key={index}
              className={`edit-profile__photo-slot ${index === 0 ? 'edit-profile__photo-slot--main' : ''}`}
              onClick={() => !photo && handlePhotoUpload(index)}
            >
              {photo ? (
                <>
                  <img src={photo} alt={`Photo ${index + 1}`} className="edit-profile__photo-img" />
                  <button
                    className="edit-profile__photo-remove"
                    onClick={(e) => { e.stopPropagation(); removePhoto(index); }}
                  >
                    <X size={14} />
                  </button>
                </>
              ) : (
                <div className="edit-profile__photo-placeholder">
                  <Plus size={20} />
                  {index === 0 && <span>Main</span>}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Name */}
      <div className="edit-profile__section">
        <h3 className="edit-profile__section-title">Display Name</h3>
        <input
          type="text"
          className="edit-profile__input"
          value={displayName}
          onChange={(e) => setDisplayName(e.target.value)}
          placeholder="Your display name"
          maxLength={30}
        />
      </div>

      {/* Bio */}
      <div className="edit-profile__section">
        <h3 className="edit-profile__section-title">About Me</h3>
        <textarea
          className="edit-profile__textarea"
          value={bio}
          onChange={(e) => setBio(e.target.value)}
          placeholder="Write something about yourself..."
          maxLength={300}
          rows={4}
        />
        <span className="edit-profile__char-count">{bio.length}/300</span>
      </div>

      {/* Interests */}
      <div className="edit-profile__section">
        <h3 className="edit-profile__section-title">
          Interests <span className="edit-profile__badge">{selectedInterests.length}/8</span>
        </h3>
        <div className="edit-profile__interests">
          {INTEREST_OPTIONS.map((interest) => (
            <button
              key={interest}
              className={`edit-profile__interest-chip ${selectedInterests.includes(interest) ? 'edit-profile__interest-chip--active' : ''}`}
              onClick={() => toggleInterest(interest)}
            >
              {selectedInterests.includes(interest) && <Check size={14} />}
              {interest}
            </button>
          ))}
        </div>
      </div>

      {/* Prompts */}
      <div className="edit-profile__section">
        <h3 className="edit-profile__section-title">Conversation Starters</h3>
        <div className="edit-profile__prompts">
          {ICEBREAKER_QUESTIONS.slice(0, 3).map((q, i) => (
            <div key={i} className="edit-profile__prompt">
              <p className="edit-profile__prompt-question">{q}</p>
              <input
                type="text"
                className="edit-profile__prompt-input"
                placeholder="Add your answer..."
              />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
