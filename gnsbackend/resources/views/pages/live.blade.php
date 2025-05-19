@extends('layout.app')

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
