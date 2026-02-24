import { useState } from 'react';
import { MapPin, Filter, MoreVertical } from 'lucide-react';
import './NearbyPage.css';

// Mock data for nearby users
const MOCK_NEARBY = [];

export default function NearbyPage() {
  const [filterActive, setFilterActive] = useState(false);

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
          onClick={() => setFilterActive(!filterActive)}
        >
          <Filter size={20} />
        </button>
      </header>

      {/* Grid */}
      <div className="nearby-page__grid">
        {MOCK_NEARBY.map((user) => (
          <div key={user.id} className="nearby-card">
            <div className="nearby-card__image-container">
              <img src={user.image} alt={user.name} className="nearby-card__image" />
              <div className="nearby-card__gradient"></div>
              
              {/* Online status indicator */}
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
    </div>
  );
}
