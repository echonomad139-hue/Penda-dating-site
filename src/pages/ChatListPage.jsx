import { useNavigate } from 'react-router-dom';
import { Search, MessageCircle } from 'lucide-react';
import EmptyState from '../components/UI/EmptyState';
import './ChatListPage.css';

const DEMO_CHATS = [];

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

      {DEMO_CHATS.length === 0 ? (
        <EmptyState
          icon={MessageCircle}
          title="No messages yet"
          message="Match with someone on the Discover page to start a conversation!"
          action={() => navigate('/discover')}
          actionLabel="Start Discovering"
        />
      ) : (
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
      )}
    </div>
  );
}
