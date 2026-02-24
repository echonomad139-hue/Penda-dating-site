import './Skeleton.css';

export default function Skeleton({ width, height, borderRadius, className = '', variant = 'rect' }) {
  const style = {
    width: width || '100%',
    height: height || (variant === 'circle' ? width : '16px'),
    borderRadius: variant === 'circle' ? '50%' : (borderRadius || '8px'),
  };

  return <div className={`skeleton-loader ${className}`} style={style} />;
}

export function SkeletonCard() {
  return (
    <div className="skeleton-card">
      <Skeleton height="240px" borderRadius="12px" />
      <div className="skeleton-card__body">
        <Skeleton width="60%" height="20px" />
        <Skeleton width="40%" height="14px" />
        <Skeleton width="80%" height="14px" />
      </div>
    </div>
  );
}

export function SkeletonChat() {
  return (
    <div className="skeleton-chat">
      <Skeleton variant="circle" width="48px" />
      <div className="skeleton-chat__text">
        <Skeleton width="70%" height="14px" />
        <Skeleton width="45%" height="12px" />
      </div>
    </div>
  );
}
