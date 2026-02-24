import { useState } from 'react';
import { X, SlidersHorizontal } from 'lucide-react';
import { GENDER_OPTIONS } from '../../config';
import './FilterModal.css';

export default function FilterModal({ onClose, onApply, initialFilters }) {
  const [ageRange, setAgeRange] = useState(initialFilters?.ageRange || [18, 50]);
  const [distance, setDistance] = useState(initialFilters?.distance || 50);
  const [gender, setGender] = useState(initialFilters?.gender || 'All');

  const handleApply = () => {
    onApply({ ageRange, distance, gender });
    onClose();
  };

  const handleReset = () => {
    setAgeRange([18, 50]);
    setDistance(50);
    setGender('All');
  };

  return (
    <div className="filter-modal" onClick={onClose}>
      <div className="filter-modal__sheet animate-slide-up" onClick={(e) => e.stopPropagation()}>
        <div className="filter-modal__header">
          <h3 className="filter-modal__title">
            <SlidersHorizontal size={18} /> Preferences
          </h3>
          <button className="filter-modal__close" onClick={onClose}>
            <X size={20} />
          </button>
        </div>

        {/* Age Range */}
        <div className="filter-modal__section">
          <label className="filter-modal__label">
            Age Range
            <span className="filter-modal__value">{ageRange[0]} – {ageRange[1]}</span>
          </label>
          <div className="filter-modal__range-group">
            <input
              type="range"
              min="18"
              max="65"
              value={ageRange[0]}
              onChange={(e) => setAgeRange([Math.min(+e.target.value, ageRange[1] - 1), ageRange[1]])}
              className="filter-modal__slider"
            />
            <input
              type="range"
              min="18"
              max="65"
              value={ageRange[1]}
              onChange={(e) => setAgeRange([ageRange[0], Math.max(+e.target.value, ageRange[0] + 1)])}
              className="filter-modal__slider"
            />
          </div>
        </div>

        {/* Distance */}
        <div className="filter-modal__section">
          <label className="filter-modal__label">
            Distance
            <span className="filter-modal__value">{distance} km</span>
          </label>
          <input
            type="range"
            min="1"
            max="200"
            value={distance}
            onChange={(e) => setDistance(+e.target.value)}
            className="filter-modal__slider"
          />
        </div>

        {/* Gender */}
        <div className="filter-modal__section">
          <label className="filter-modal__label">Show Me</label>
          <div className="filter-modal__chips">
            {['All', ...GENDER_OPTIONS].map((opt) => (
              <button
                key={opt}
                className={`filter-modal__chip ${gender === opt ? 'filter-modal__chip--active' : ''}`}
                onClick={() => setGender(opt)}
              >
                {opt}
              </button>
            ))}
          </div>
        </div>

        {/* Actions */}
        <div className="filter-modal__actions">
          <button className="filter-modal__btn filter-modal__btn--reset" onClick={handleReset}>
            Reset
          </button>
          <button className="filter-modal__btn filter-modal__btn--apply" onClick={handleApply}>
            Apply Filters
          </button>
        </div>
      </div>
    </div>
  );
}
