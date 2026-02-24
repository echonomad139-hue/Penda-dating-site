import { useNavigate } from 'react-router-dom';
import { Search } from 'lucide-react';
import { SkeletonChat } from '../components/UI/Skeleton';
import useChatStore from '../store/chatSlice';
import './ChatListPage.css';

const DEMO_CHATS = [
  {
    id: 1, name: 'Amara', lastMessage: 'That sounds amazing! Tell me more...', time: '2m',
    unread: 2, online: true,
    avatar: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&h=100&fit=crop',
  },
  {
    id: 2, name: 'Kwame', lastMessage: 'When are you free to chat?', time: '1h',
    unread: 0, online: true,
    avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop',
  },
  {
    id: 3, name: 'Fatima', lastMessage: 'I love that poem you shared ✨', time: '3h',
    unread: 0, online: false,
    avatar: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&h=100&fit=crop',
  },
];

export default function ChatListPage() {
  const navigate = useNavigate();

  return (
    <div className="chat-list">
      <div className="chat-list__header">
        <h2 className="chat-list__title">Messages</h2>
      </div>

      <div className="chat-list__search">
        <Search size={16} className="chat-list__search-icon" />
        <input
          type="text"
          placeholder="Search conversations..."
          className="chat-list__search-input"
        />
      </div>

      <div className="chat-list__items">
        {DEMO_CHATS.map((chat) => (
          <button
            key={chat.id}
            className="chat-list__item"
            onClick={() => navigate(`/chat/${chat.id}`)}
          >
            <div className="chat-list__avatar-wrapper">
              <img src={chat.avatar} alt={chat.name} className="chat-list__avatar" />
              {chat.online && <span className="chat-list__online-dot" />}
            </div>
            <div className="chat-list__content">
              <div className="chat-list__row">
                <span className="chat-list__name">{chat.name}</span>
                <span className="chat-list__time">{chat.time}</span>
              </div>
              <div className="chat-list__row">
                <span className="chat-list__message">{chat.lastMessage}</span>
                {chat.unread > 0 && (
                  <span className="chat-list__unread">{chat.unread}</span>
                )}
              </div>
            </div>
          </button>
        ))}
      </div>
    </div>
  );
}
