import { useState, useRef, useEffect, useMemo } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Send, Smile } from 'lucide-react';
import './ChatRoomPage.css';

const DEMO_CONTACTS = {
  1: { name: 'Amara', avatar: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=100&h=100&fit=crop', online: true },
  2: { name: 'Kwame', avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop', online: true },
  3: { name: 'Fatima', avatar: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=100&h=100&fit=crop', online: false },
};

const DEMO_MESSAGES = [
  { id: 1, sender: 'them', text: 'Hey! I loved your profile. What brings you to PENDA?', time: '2:30 PM' },
  { id: 2, sender: 'me', text: 'Thanks! I\'m looking for real connections across Africa. Your photography is beautiful btw!', time: '2:32 PM' },
  { id: 3, sender: 'them', text: 'That means so much! I capture stories through my lens. Where are you based?', time: '2:33 PM' },
  { id: 4, sender: 'me', text: 'I\'m in Nairobi! You?', time: '2:35 PM' },
  { id: 5, sender: 'them', text: 'Mombasa! So close 😊 That sounds amazing! Tell me more...', time: '2:36 PM' },
];

export default function ChatRoomPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const [messages, setMessages] = useState(DEMO_MESSAGES);
  const [inputText, setInputText] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const messagesEndRef = useRef(null);

  const contact = DEMO_CONTACTS[id] || DEMO_CONTACTS[1];

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const handleSend = () => {
    if (!inputText.trim()) return;
    const newMsg = {
      id: Date.now(),
      sender: 'me',
      text: inputText.trim(),
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };
    setMessages((prev) => [...prev, newMsg]);
    setInputText('');

    // Simulate typing
    setIsTyping(true);
    setTimeout(() => {
      setIsTyping(false);
      setMessages((prev) => [
        ...prev,
        {
          id: Date.now() + 1,
          sender: 'them',
          text: 'That\'s really interesting! 😄',
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        },
      ]);
    }, 2000);
  };

  return (
    <div className="chat-room">
      {/* Header */}
      <div className="chat-room__header">
        <button className="chat-room__back" onClick={() => navigate('/chats')}>
          <ArrowLeft size={22} />
        </button>
        <img src={contact.avatar} alt={contact.name} className="chat-room__avatar" />
        <div className="chat-room__info">
          <span className="chat-room__name">{contact.name}</span>
          <span className={`chat-room__status ${contact.online ? 'chat-room__status--online' : ''}`}>
            {contact.online ? 'Online' : 'Last seen recently'}
          </span>
        </div>
      </div>

      {/* Messages */}
      <div className="chat-room__messages">
        {messages.map((msg) => (
          <div
            key={msg.id}
            className={`chat-room__bubble ${msg.sender === 'me' ? 'chat-room__bubble--me' : 'chat-room__bubble--them'}`}
          >
            <p className="chat-room__bubble-text">{msg.text}</p>
            <span className="chat-room__bubble-time">{msg.time}</span>
          </div>
        ))}

        {isTyping && (
          <div className="chat-room__typing">
            <span className="chat-room__typing-dot" />
            <span className="chat-room__typing-dot" />
            <span className="chat-room__typing-dot" />
          </div>
        )}

        <div ref={messagesEndRef} />
      </div>

      {/* Input */}
      <div className="chat-room__input-bar">
        <button className="chat-room__emoji-btn">
          <Smile size={22} />
        </button>
        <input
          type="text"
          className="chat-room__input"
          placeholder="Type a message..."
          value={inputText}
          onChange={(e) => setInputText(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && handleSend()}
        />
        <button
          className={`chat-room__send-btn ${inputText.trim() ? 'chat-room__send-btn--active' : ''}`}
          onClick={handleSend}
          disabled={!inputText.trim()}
        >
          <Send size={18} />
        </button>
      </div>
    </div>
  );
}
