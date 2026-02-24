import './EmptyState.css';

export default function EmptyState({ icon: Icon, title, message, action, actionLabel }) {
  return (
    <div className="empty-state animate-fade-in">
      <div className="empty-state__icon-ring">
        {Icon && <Icon size={40} strokeWidth={1.4} />}
      </div>
      <h3 className="empty-state__title">{title}</h3>
      <p className="empty-state__message">{message}</p>
      {action && (
        <button className="empty-state__btn" onClick={action}>
          {actionLabel || 'Get Started'}
        </button>
      )}
    </div>
  );
}
