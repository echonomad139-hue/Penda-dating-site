import { useState } from 'react';
import { MapPin, Filter, MoreVertical } from 'lucide-react';
import './NearbyPage.css';

// Mock data for nearby users
const MOCK_NEARBY = [
  {
    id: 1,
    name: 'Aisha',
    age: 26,
    distance: '1.2 km away',
    image: 'https://images.unsplash.com/photo-1531123897727-8f129e1bf98c?w=400&h=500&fit=crop',
    online: true,
  },
  {
    id: 2,
    name: 'Samuel',
    age: 29,
    distance: '2.5 km away',
    image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&h=500&fit=crop',
    online: false,
    lastActive: '2h ago',
  },
  {
    id: 3,
    name: 'Nia',
    age: 24,
    distance: '3.0 km away',
    image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=500&fit=crop',
    online: true,
  },
  {
    id: 4,
    name: 'David',
    age: 31,
    distance: '4.1 km away',
    image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=500&fit=crop',
    online: false,
    lastActive: '1m ago',
  },
  {
    id: 5,
    name: 'Zara',
    age: 27,
    distance: '5.5 km away',
    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=500&fit=crop',
    online: true,
  },
  {
    id: 6,
    name: 'Kwame',
    age: 33,
    distance: '6.2 km away',
    image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&h=500&fit=crop',
    online: true,
  },
];

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
