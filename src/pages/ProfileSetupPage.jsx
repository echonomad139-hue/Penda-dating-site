import { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { Camera, Plus, X } from 'lucide-react';
import Button from '../components/UI/Button';
import Input from '../components/UI/Input';
import useAuthStore from '../store/authSlice';
import './ProfileSetupPage.css';

const INTEREST_OPTIONS = [
  'Travel', 'Music', 'Photography', 'Cooking', 'Fitness', 'Art',
  'Reading', 'Dancing', 'Gaming', 'Nature', 'Movies', 'Fashion',
  'Technology', 'Poetry', 'Coffee', 'Entrepreneurship', 'Sports', 'Meditation',
];

export default function ProfileSetupPage() {
  const navigate = useNavigate();
  const { updateProfile } = useAuthStore();
  const [photos, setPhotos] = useState([null, null, null, null]);
  const [bio, setBio] = useState('');
  const [selectedInterests, setSelectedInterests] = useState([]);
  const [saving, setSaving] = useState(false);

  const handleContinue = async () => {
    setSaving(true);
    await updateProfile({ photos, bio, interests: selectedInterests });
    setSaving(false);
    navigate('/discover');
  };

  const completionPercent = useMemo(() => {
    let score = 0;
    if (photos.some((p) => p !== null)) score += 40;
    if (bio.length > 10) score += 30;
    if (selectedInterests.length >= 3) score += 30;
    return score;
  }, [photos, bio, selectedInterests]);

  const handlePhotoUpload = (index) => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => {
          const newPhotos = [...photos];
          newPhotos[index] = ev.target.result;
          setPhotos(newPhotos);
        };
        reader.readAsDataURL(file);
      }
    };
    input.click();
  };

  const removePhoto = (index) => {
    const newPhotos = [...photos];
    newPhotos[index] = null;
    setPhotos(newPhotos);
  };

  const toggleInterest = (interest) => {
    setSelectedInterests((prev) =>
      prev.includes(interest)
        ? prev.filter((i) => i !== interest)
        : prev.length < 6
          ? [...prev, interest]
          : prev
    );
  };

  return (
    <div className="profile-setup">
      {/* Completion Meter */}
      <div className="profile-setup__meter">
        <div className="profile-setup__meter-bar">
          <div
            className="profile-setup__meter-fill"
            style={{ width: `${completionPercent}%` }}
          />
        </div>
        <span className="profile-setup__meter-label">{completionPercent}% complete</span>
      </div>

      <h1 className="profile-setup__title">Make it yours</h1>
      <p className="profile-setup__subtitle">Show the world who you are</p>

      {/* Photos */}
      <section className="profile-setup__section">
        <h3 className="profile-setup__section-title">Your photos</h3>
        <div className="profile-setup__photos">
          {photos.map((photo, i) => (
            <div
              key={i}
              className={`profile-setup__photo-slot ${i === 0 ? 'profile-setup__photo-slot--main' : ''}`}
              onClick={() => !photo && handlePhotoUpload(i)}
            >
              {photo ? (
                <>
                  <img src={photo} alt={`Photo ${i + 1}`} />
                  <button
                    className="profile-setup__photo-remove"
                    onClick={(e) => { e.stopPropagation(); removePhoto(i); }}
                  >
                    <X size={14} />
                  </button>
                </>
              ) : (
                <div className="profile-setup__photo-placeholder">
                  {i === 0 ? <Camera size={24} /> : <Plus size={20} />}
                  {i === 0 && <span>Main photo</span>}
                </div>
              )}
            </div>
          ))}
        </div>
      </section>

      {/* Bio */}
      <section className="profile-setup__section">
        <h3 className="profile-setup__section-title">About you</h3>
        <textarea
          className="profile-setup__bio"
          placeholder="What makes you, you? Write something real..."
          value={bio}
          onChange={(e) => setBio(e.target.value)}
          maxLength={300}
          rows={4}
        />
        <span className="profile-setup__bio-count">{bio.length}/300</span>
      </section>

      {/* Interests */}
      <section className="profile-setup__section">
        <h3 className="profile-setup__section-title">
          Your interests <span className="profile-setup__section-hint">(pick up to 6)</span>
        </h3>
        <div className="profile-setup__interests">
          {INTEREST_OPTIONS.map((interest) => (
            <button
              key={interest}
              className={`profile-setup__interest-tag ${
                selectedInterests.includes(interest) ? 'profile-setup__interest-tag--active' : ''
              }`}
              onClick={() => toggleInterest(interest)}
            >
              {interest}
            </button>
          ))}
        </div>
      </section>

      <Button
        fullWidth
        onClick={handleContinue}
        disabled={completionPercent < 40 || saving}
        loading={saving}
      >
        Continue to Discover
      </Button>
    </div>
  );
}
