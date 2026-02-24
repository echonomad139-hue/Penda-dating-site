import { useState } from 'react';
import { MapPin, Filter, MoreVertical, Users } from 'lucide-react';
import EmptyState from '../components/UI/EmptyState';
import FilterModal from '../components/UI/FilterModal';
import FullProfileModal from '../components/UI/FullProfileModal';
import './NearbyPage.css';

const MOCK_NEARBY = [];

export default function NearbyPage() {
  const [filterActive, setFilterActive] = useState(false);
  const [showFilters, setShowFilters] = useState(false);
  const [selectedProfile, setSelectedProfile] = useState(null);
  const [filters, setFilters] = useState({ ageRange: [18, 50], distance: 50, gender: 'All' });

  return (
    <div className="nearby-page">
      {/* Header */}
      <header className="nearby-page__header">
        <div>
          <h1 className="nearby-page__title">
            <MapPin size={22} className="nearby-page__title-icon" />
            Nearby You
          </h1>
          <p className="nearby-page__subtitle">People looking for connection around you</p>
        </div>
        <button
          className={`nearby-page__filter-btn ${filterActive ? 'active' : ''}`}
          onClick={() => { setFilterActive(!filterActive); setShowFilters(true); }}
        >
          <Filter size={20} />
        </button>
      </header>

      {/* Grid or empty state */}
      {MOCK_NEARBY.length === 0 ? (
        <EmptyState
          icon={Users}
          title="No one nearby"
          message="Enable location access or try again later to see people near you."
          action={() => setShowFilters(true)}
          actionLabel="Adjust Filters"
        />
      ) : (
        <div className="nearby-page__grid">
          {MOCK_NEARBY.map((user) => (
            <div
              key={user.id}
              className="nearby-card"
              onClick={() => setSelectedProfile(user)}
            >
              <div className="nearby-card__image-container">
                <img src={user.image} alt={user.name} className="nearby-card__image" />
                <div className="nearby-card__gradient"></div>
                <div className="nearby-card__status">
                  <span className={`nearby-card__status-dot ${user.online ? 'online' : 'offline'}`}></span>
                  {user.online ? 'Online' : user.lastActive}
                </div>
              </div>
              <div className="nearby-card__info">
                <div className="nearby-card__info-top">
                  <span className="nearby-card__name">{user.name}, {user.age}</span>
                  <button className="nearby-card__more" aria-label="More options">
                    <MoreVertical size={16} />
                  </button>
                </div>
                <div className="nearby-card__distance">
                  <MapPin size={12} />
                  {user.distance}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Filter Modal */}
      {showFilters && (
        <FilterModal
          onClose={() => setShowFilters(false)}
          onApply={(f) => setFilters(f)}
          initialFilters={filters}
        />
      )}

      {/* Full Profile Modal */}
      {selectedProfile && (
        <FullProfileModal
          profile={selectedProfile}
          onClose={() => setSelectedProfile(null)}
          onLike={() => setSelectedProfile(null)}
          onMessage={() => setSelectedProfile(null)}
        />
      )}
    </div>
  );
}
