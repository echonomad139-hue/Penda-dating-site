import './Input.css';

export default function Input({
  label,
  type = 'text',
  name,
  value,
  onChange,
  onBlur,
  placeholder,
  error,
  touched,
  icon,
  className = '',
  ...props
}) {
  const hasError = touched && error;

  return (
    <div className={`input-group ${hasError ? 'input-group--error' : ''} ${className}`}>
      {label && (
        <label htmlFor={name} className="input-group__label">
          {label}
        </label>
      )}
      <div className="input-group__wrapper">
        {icon && <span className="input-group__icon">{icon}</span>}
        <input
          id={name}
          type={type}
          name={name}
          value={value}
          onChange={onChange}
          onBlur={onBlur}
          placeholder={placeholder}
          className={`input-group__field ${icon ? 'input-group__field--with-icon' : ''}`}
          {...props}
        />
      </div>
      {hasError && <span className="input-group__error">{error}</span>}
    </div>
  );
}
