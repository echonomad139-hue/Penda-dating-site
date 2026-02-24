import './Dropdown.css';

export default function Dropdown({
  label,
  name,
  value,
  onChange,
  onBlur,
  options = [],
  placeholder = 'Select...',
  error,
  touched,
  searchable = false,
  className = '',
  ...props
}) {
  const hasError = touched && error;

  return (
    <div className={`dropdown-group ${hasError ? 'dropdown-group--error' : ''} ${className}`}>
      {label && (
        <label htmlFor={name} className="dropdown-group__label">
          {label}
        </label>
      )}
      <select
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        onBlur={onBlur}
        className="dropdown-group__select"
        {...props}
      >
        <option value="" disabled>
          {placeholder}
        </option>
        {options.map((opt) => {
          const val = typeof opt === 'string' ? opt : opt.value;
          const display = typeof opt === 'string' ? opt : opt.label;
          return (
            <option key={val} value={val}>
              {display}
            </option>
          );
        })}
      </select>
      {hasError && <span className="dropdown-group__error">{error}</span>}
    </div>
  );
}
