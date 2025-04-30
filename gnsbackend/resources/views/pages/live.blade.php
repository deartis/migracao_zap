@extends('layout.app')

@section('styles')
    <style>
        .chat-container {
            display: flex;
            height: 85vh;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .contacts-panel {
            width: 35%;
            background-color: #FFFFFF;
            border-right: 1px solid #E2E8F0;
            overflow-y: auto;
        }

        .chat-panel {
            width: 65%;
            display: flex;
            flex-direction: column;
            background-color: #E5DDD5;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23bbb' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #E2E8F0;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .contact-item:hover {
            background-color: #F8FAFC;
        }

        .contact-item.active {
            background-color: #E5DDD5;
        }

        .contact-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .contact-info {
            flex: 1;
        }

        .contact-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .contact-last-message {
            color: #64748B;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 190px;
        }

        .contact-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .contact-time {
            color: #64748B;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }

        .contact-unread {
            background-color: #25D366;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .search-box {
            padding: 10px 15px;
            background-color: #F0F2F5;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            background-color: white;
        }

        .chat-header {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #F0F2F5;
            border-bottom: 1px solid #E2E8F0;
        }

        .chat-header .contact-avatar {
            width: 40px;
            height: 40px;
        }

        .chat-header .contact-info {
            margin-left: 15px;
        }

        .chat-header .header-actions {
            margin-left: auto;
        }

        .chat-header .header-actions i {
            margin-left: 15px;
            color: #54656F;
            cursor: pointer;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
            max-width: 65%;
            padding: 10px 15px;
            border-radius: 7.5px;
            position: relative;
            clear: both;
        }

        .message-time {
            font-size: 0.7rem;
            color: #64748B;
            text-align: right;
            margin-top: 5px;
        }

        .message-received {
            background-color: white;
            float: left;
            border-top-left-radius: 0;
        }

        .message-sent {
            background-color: #DCF8C6;
            float: right;
            border-top-right-radius: 0;
        }

        .chat-input {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #F0F2F5;
        }

        .chat-input input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            margin: 0 10px;
        }

        .chat-input i {
            color: #54656F;
            cursor: pointer;
            font-size: 1.3rem;
        }

        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: #F0F2F5;
            text-align: center;
            color: #667781;
        }

        .no-chat-selected i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h3 class="mb-4">WhatsApp Chat</h3>

                <div class="chat-container">
                    <!-- Contacts Panel -->
                    <div class="contacts-panel">
                        <div class="search-box">
                            <input type="text" id="contact-search" placeholder="Pesquisar ou começar nova conversa">
                        </div>

                        <div id="contacts-list">
                            <div class="loading-indicator" id="contacts-loading">
                                Carregando contatos...
                            </div>
                            <!-- Contacts will be loaded here dynamically -->
                        </div>
                    </div>

                    <!-- Chat Panel -->
                    <div class="chat-panel">
                        <div id="no-chat-selected" class="no-chat-selected">
                            <i class="fas fa-comments"></i>
                            <h4>WhatsApp Web</h4>
                            <p>Selecione um contato para iniciar uma conversa.</p>
                        </div>

                        <div id="chat-content" style="display: none; height: 100%; flex-direction: column;">
                            <div class="chat-header">
                                <img src="" id="current-contact-avatar" class="contact-avatar" alt="">
                                <div class="contact-info">
                                    <div class="contact-name" id="current-contact-name"></div>
                                    <div class="contact-status" id="current-contact-status"></div>
                                </div>
                                <div class="header-actions">
                                    <i class="fas fa-search"></i>
                                    <i class="fas fa-ellipsis-v"></i>
                                </div>
                            </div>

                            <div class="chat-messages" id="messages-container">
                                <div class="loading-indicator" id="messages-loading">
                                    Carregando mensagens...
                                </div>
                                <!-- Messages will be loaded here dynamically -->
                            </div>

                            <div class="chat-input">
                                <i class="far fa-smile"></i>
                                <i class="fas fa-paperclip"></i>
                                <input type="text" id="message-input" placeholder="Digite uma mensagem">
                                <i class="fas fa-microphone" id="voice-btn"></i>
                                <i class="fas fa-paper-plane" id="send-btn" style="display: none;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const contactsList = document.getElementById('contacts-list');
            const contactsLoading = document.getElementById('contacts-loading');
            const contactSearch = document.getElementById('contact-search');
            const noChatSelected = document.getElementById('no-chat-selected');
            const chatContent = document.getElementById('chat-content');
            const messagesContainer = document.getElementById('messages-container');
            const messagesLoading = document.getElementById('messages-loading');
            const messageInput = document.getElementById('message-input');
            const sendBtn = document.getElementById('send-btn');
            const voiceBtn = document.getElementById('voice-btn');

            // Current state
            let currentContactId = null;
            let contacts = [];
            let messages = {};

            // Initialize
            init();

            function init() {
                // Show loading indicators
                contactsLoading.style.display = 'block';

                // Load contacts (from whatsapp_web.js API)
                loadContacts();

                // Setup event listeners
                setupEventListeners();
            }

            function loadContacts() {
                // Example API call to fetch contacts
                fetch('/api/whatsapp/contacts')
                    .then(response => response.json())
                    .then(data => {
                        contacts = data;
                        renderContacts();
                        contactsLoading.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error loading contacts:', error);
                        contactsLoading.textContent = 'Erro ao carregar contatos. Tente novamente.';
                    });
            }

            function renderContacts() {
                contactsList.innerHTML = '';

                contacts.forEach(contact => {
                    const contactEl = createContactElement(contact);
                    contactsList.appendChild(contactEl);
                });
            }

            function createContactElement(contact) {
                const div = document.createElement('div');
                div.className = 'contact-item';
                div.dataset.id = contact.id;

                // Generate avatar placeholder if no image
                const avatarSrc = contact.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.name)}&background=random`;

                div.innerHTML = `
                <img src="${avatarSrc}" class="contact-avatar" alt="${contact.name}">
                <div class="contact-info">
                    <div class="contact-name">${contact.name}</div>
                    <div class="contact-last-message">${contact.lastMessage || ''}</div>
                </div>
                <div class="contact-meta">
                    <div class="contact-time">${contact.lastMessageTime || ''}</div>
                    ${contact.unreadCount ? `<div class="contact-unread">${contact.unreadCount}</div>` : ''}
                </div>
            `;

                div.addEventListener('click', () => {
                    selectContact(contact.id);
                });

                return div;
            }

            function selectContact(contactId) {
                // Update UI for selected contact
                document.querySelectorAll('.contact-item').forEach(el => {
                    el.classList.remove('active');
                    if (el.dataset.id === contactId) {
                        el.classList.add('active');
                    }
                });

                // Update current contact
                currentContactId = contactId;
                const contact = contacts.find(c => c.id === contactId);

                // Update chat header
                document.getElementById('current-contact-avatar').src = contact.avatar ||
                    `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.name)}&background=random`;
                document.getElementById('current-contact-name').textContent = contact.name;
                document.getElementById('current-contact-status').textContent = contact.status || 'online';

                // Show chat content
                noChatSelected.style.display = 'none';
                chatContent.style.display = 'flex';

                // Load messages for this contact
                loadMessages(contactId);
            }

            function loadMessages(contactId) {
                // Show loading indicator
                messagesContainer.innerHTML = '';
                messagesLoading.style.display = 'block';

                // If we already have messages cached
                if (messages[contactId]) {
                    renderMessages(contactId);
                    messagesLoading.style.display = 'none';
                    return;
                }

                // Example API call to fetch messages
                fetch(`/api/whatsapp/messages/${contactId}`)
                    .then(response => response.json())
                    .then(data => {
                        messages[contactId] = data;
                        renderMessages(contactId);
                        messagesLoading.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error loading messages:', error);
                        messagesLoading.textContent = 'Erro ao carregar mensagens. Tente novamente.';
                    });
            }

            function renderMessages(contactId) {
                messagesContainer.innerHTML = '';

                if (!messages[contactId] || messages[contactId].length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'text-center my-5 text-muted';
                    emptyState.textContent = 'Nenhuma mensagem ainda. Comece a conversar!';
                    messagesContainer.appendChild(emptyState);
                    return;
                }

                messages[contactId].forEach(msg => {
                    const messageEl = createMessageElement(msg);
                    messagesContainer.appendChild(messageEl);
                });

                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            function createMessageElement(message) {
                const div = document.createElement('div');
                div.className = `message ${message.fromMe ? 'message-sent' : 'message-received'}`;

                div.innerHTML = `
                <div class="message-content">${message.body}</div>
                <div class="message-time">${formatTime(message.timestamp)} ${message.fromMe ? '✓✓' : ''}</div>
            `;

                return div;
            }

            function sendMessage() {
                const text = messageInput.value.trim();
                if (!text || !currentContactId) return;

                // Clear input
                messageInput.value = '';

                // Create temporary message object
                const tempMessage = {
                    id: Date.now().toString(),
                    body: text,
                    timestamp: new Date(),
                    fromMe: true,
                    status: 'sending'
                };

                // Add to local messages and render
                if (!messages[currentContactId]) {
                    messages[currentContactId] = [];
                }
                messages[currentContactId].push(tempMessage);
                renderMessages(currentContactId);

                // Send to API
                fetch('/api/whatsapp/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        contactId: currentContactId,
                        message: text
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        // Update message status
                        const index = messages[currentContactId].findIndex(m => m.id === tempMessage.id);
                        if (index !== -1) {
                            messages[currentContactId][index].status = 'sent';
                            messages[currentContactId][index].id = data.id;
                            renderMessages(currentContactId);
                        }

                        // Update contact's last message
                        updateContactLastMessage(currentContactId, text);
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        // Update message status to error
                        const index = messages[currentContactId].findIndex(m => m.id === tempMessage.id);
                        if (index !== -1) {
                            messages[currentContactId][index].status = 'error';
                            renderMessages(currentContactId);
                        }
                    });
            }

            function updateContactLastMessage(contactId, text) {
                const contact = contacts.find(c => c.id === contactId);
                if (contact) {
                    contact.lastMessage = text;
                    contact.lastMessageTime = formatTime(new Date());
                    renderContacts();
                }
            }

            function setupEventListeners() {
                // Send message on button click
                sendBtn.addEventListener('click', sendMessage);

                // Send message on Enter key press
                messageInput.addEventListener('keypress', event => {
                    if (event.key === 'Enter') {
                        sendMessage();
                    }
                });

                // Show send button when typing
                messageInput.addEventListener('input', () => {
                    if (messageInput.value.trim()) {
                        voiceBtn.style.display = 'none';
                        sendBtn.style.display = 'block';
                    } else {
                        voiceBtn.style.display = 'block';
                        sendBtn.style.display = 'none';
                    }
                });

                // Filter contacts on search
                contactSearch.addEventListener('input', () => {
                    const query = contactSearch.value.toLowerCase();
                    const filteredContacts = contacts.filter(contact =>
                        contact.name.toLowerCase().includes(query) ||
                        (contact.lastMessage && contact.lastMessage.toLowerCase().includes(query))
                    );

                    contactsList.innerHTML = '';
                    filteredContacts.forEach(contact => {
                        const contactEl = createContactElement(contact);
                        contactsList.appendChild(contactEl);
                    });
                });
            }

            // Helper function to format time
            function formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            // Setup WebSocket or Event Source for real-time updates
            function setupRealTimeListeners() {
                // Example of setting up a WebSocket connection
                const ws = new WebSocket('ws://your-websocket-server/whatsapp');

                ws.onmessage = function(event) {
                    const data = JSON.parse(event.data);

                    if (data.type === 'new_message') {
                        // Handle new message
                        const message = data.message;

                        // Add to messages if chat is open
                        if (messages[message.contactId]) {
                            messages[message.contactId].push(message);

                            // Re-render if it's the current chat
                            if (currentContactId === message.contactId) {
                                renderMessages(currentContactId);
                            }
                        }

                        // Update contact's last message
                        updateContactLastMessage(message.contactId, message.body);
                    }
                    else if (data.type === 'status_update') {
                        // Handle contact status updates
                        // ...
                    }
                };

                ws.onerror = function(error) {
                    console.error('WebSocket error:', error);
                };
            }

            // Uncomment to enable real-time updates:
            // setupRealTimeListeners();
        });
    </script>
@endsection

{{--
@extends('layout.app')

@section('content')
    <div class="container" style="max-width: 600px; margin: 0 auto; padding-top: 30px;">
        <h2>Responder Mensagem</h2>

        @if(session('success'))
            <div style="color: green; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('whatsapp.responder') }}" method="POST">
            @csrf

            <div style="margin-bottom: 15px;">
                <label for="numero" style="display:block; margin-bottom:5px;">Número do WhatsApp:</label>
                <input type="text" id="numero" name="numero" required
                       placeholder="Ex: 5598999999999"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="mensagem" style="display:block; margin-bottom:5px;">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" required
                          placeholder="Digite sua resposta aqui..."
                          rows="5"
                          style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: #28a745; border: none; color: white; border-radius: 5px; cursor: pointer;">
                Enviar Resposta
            </button>
        </form>
    </div>
@endsection
--}}
