document.addEventListener('DOMContentLoaded', function() {
    class MessagingSystem {
        constructor() {
            this.currentUserId = null;
            this.currentConversationId = null;
            this.currentRecipientId = null;
            this.currentRecipientName = null;
            this.pollingInterval = null;
            this.lastMessageId = 0;
            this.isModalOpen = false;
            this.initialize();
        }

        async initialize() {
            console.log('Initializing messaging system...');
            await this.loadCurrentUser();
            this.initEventListeners();
        }

async loadCurrentUser() {
    try {
        console.log('Tentative de chargement de l\'utilisateur...');
        const response = await fetch('get-current-user.php', {
            credentials: 'include',
            headers: {
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                showNotification('error', 'Session expirée. Veuillez vous reconnecter.');
                window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                return;
            }
            throw new Error(`Erreur HTTP! statut: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Données utilisateur:', data);
        
        if (data.success) {
            this.currentUserId = data.user.id;
            console.log(`Utilisateur chargé: ID ${this.currentUserId}`);
        } else {
            console.error('Échec du chargement:', data.message);
            this.handleUnauthenticatedState();
        }
    } catch (error) {
        console.error('Erreur de chargement:', error);
        this.handleUnauthenticatedState();
    }
}

        handleUnauthenticatedState() {
            console.warn('Utilisateur non authentifié, désactivation des fonctionnalités');
            const messagingElements = document.querySelectorAll('[data-messaging]');
            messagingElements.forEach(el => {
                el.style.display = 'none';
            });
            
            const notificationArea = document.getElementById('notification-area');
            if (notificationArea) {
                notificationArea.innerHTML = `
                    <div class="alert alert-warning">
                        Veuillez vous <a href="/login.php">connecter</a> pour accéder à la messagerie
                    </div>
                `;
            }
        }

        initEventListeners() {
            const messagesLink = document.getElementById('messages-link');
            if (messagesLink) {
                messagesLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleMessagingModal();
                });
            }

            const closeModalBtn = document.querySelector('.close-modal');
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', () => {
                    this.closeMessagingModal();
                });
            }

            const sendBtn = document.getElementById('send-message-btn');
            if (sendBtn) {
                sendBtn.addEventListener('click', () => {
                    this.sendMessage();
                });
            }

            const messageInput = document.getElementById('message-input');
            if (messageInput) {
                messageInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
            }

            const newConvBtn = document.getElementById('new-conversation-btn');
            if (newConvBtn) {
                newConvBtn.addEventListener('click', () => {
                    this.openNewConversationModal();
                });
            }

            const startConvBtn = document.getElementById('start-conversation-btn');
            if (startConvBtn) {
                startConvBtn.addEventListener('click', () => {
                    this.startNewConversation();
                });
            }

            console.log('Event listeners initialized');
        }

        toggleMessagingModal() {
            if (this.isModalOpen) {
                this.closeMessagingModal();
            } else {
                this.openMessagingModal();
            }
        }

    async openMessagingModal() {
    console.log('Opening messaging modal...');
    const modal = document.getElementById('messages-modal');
    if (!modal) {
        console.error('Messages modal element not found');
        showNotification('error', 'Erreur: élément de la modale introuvable');
        return;
    }
    
    modal.style.display = 'block';
    this.isModalOpen = true;
    
    const conversationsContainer = document.getElementById('conversations');
    if (conversationsContainer) {
        conversationsContainer.innerHTML = '<div class="loading">Chargement...</div>';
    }
    
    try {
        await this.loadConversations();
        this.startPolling();
        console.log('Messaging modal opened successfully');
    } catch (error) {
        console.error('Error opening messaging modal:', error);
        let errorMessage = 'Erreur de chargement des conversations';
        if (error.message.includes('401')) {
            errorMessage = 'Session expirée. Veuillez vous reconnecter.';
            window.location.href = '/login.php';
        } else if (error.message.includes('403')) {
            errorMessage = 'Utilisateur non autorisé ou inactif.';
        } else if (error.message.includes('500')) {
            errorMessage = 'Erreur serveur. Veuillez réessayer plus tard.';
        }
        showNotification('error', errorMessage);
    }
}

        closeMessagingModal() {
            console.log('Closing messaging modal...');
            const modal = document.getElementById('messages-modal');
            if (modal) {
                modal.style.display = 'none';
            }
            this.isModalOpen = false;
            this.stopPolling();
        }

        startPolling() {
            this.stopPolling();
            console.log('Starting polling with interval 3000ms');
            this.pollingInterval = setInterval(() => {
                if (this.currentConversationId) {
                    console.log('Polling: checking for new messages...');
                    this.checkNewMessages();
                }
                this.updateConversationList();
            }, 3000);
        }

        stopPolling() {
            if (this.pollingInterval) {
                console.log('Stopping polling...');
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }

        async checkNewMessages() {
            try {
                console.log(`Checking new messages for conversation ${this.currentConversationId} since ID ${this.lastMessageId}`);
                
                const response = await fetch(
                    `get-new-messages.php?conversation_id=${this.currentConversationId}&last_id=${this.lastMessageId}`, 
                    { credentials: 'include' }
                );
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('New messages response:', data);

                if (data.success && data.messages.length > 0) {
                    console.log(`Found ${data.messages.length} new messages`);
                    const container = document.getElementById('messages-display');
                    if (container) {
                        data.messages.forEach(msg => {
                            this.appendMessageToContainer(msg, container);
                            this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                        });
                        container.scrollTop = container.scrollHeight;
                    }
                }
            } catch (error) {
                console.error('Error checking new messages:', error);
            }
        }

        async updateConversationList() {
            try {
                console.log('Updating conversation list...');
                const response = await fetch('get-conversations.php', {
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Conversations data:', data);

                if (data.success) {
                    this.updateConversationsUI(data.conversations);
                    this.updateUnreadBadge(data.conversations.reduce((sum, conv) => sum + conv.unread_count, 0));
                }
            } catch (error) {
                console.error('Error updating conversation list:', error);
            }
        }

        updateConversationsUI(conversations) {
            console.log('Updating conversations UI with', conversations);
            const container = document.getElementById('conversations');
            if (!container) return;
            
            if (conversations.length === 0) {
                container.innerHTML = '<p class="no-conversations">Aucune conversation</p>';
                return;
            }
            
            container.innerHTML = '';
            
            conversations.forEach(conv => {
                const convElement = document.createElement('div');
                convElement.className = 'conversation';
                if (this.currentConversationId === conv.id) {
                    convElement.classList.add('active');
                }
                
                convElement.dataset.conversationId = conv.id;
                convElement.dataset.recipientId = conv.recipient_id;
                convElement.dataset.recipientName = conv.recipient_name;
                
                convElement.innerHTML = `
                    <div class="conversation-header">
                        <strong>${conv.recipient_name}</strong>
                        <span class="conversation-date">
                            ${this.formatDate(conv.last_message_time)}
                        </span>
                    </div>
                    <p class="conversation-preview">${this.truncateMessage(conv.last_message || 'Aucun message')}</p>
                    ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
                `;
                
                convElement.addEventListener('click', () => {
                    this.openConversation(conv.id, conv.recipient_id, conv.recipient_name);
                });
                
                container.appendChild(convElement);
            });
        }

        truncateMessage(message, maxLength = 30) {
            if (message.length <= maxLength) return message;
            return message.substring(0, maxLength) + '...';
        }

        async openConversation(conversationId, recipientId, recipientName) {
            console.log(`Opening conversation ${conversationId} with ${recipientName}`);
            
            this.currentConversationId = conversationId;
            this.currentRecipientId = recipientId;
            this.currentRecipientName = recipientName;
            this.lastMessageId = 0;
            
            const conversationWithElement = document.getElementById('conversation-with');
            if (conversationWithElement) {
                conversationWithElement.textContent = `Conversation avec ${recipientName}`;
            }
            
            document.querySelectorAll('.conversation').forEach(c => {
                c.classList.remove('active');
                if (c.dataset.conversationId === conversationId.toString()) {
                    c.classList.add('active');
                    c.querySelector('.unread-count')?.remove();
                }
            });
            
            await this.loadMessages(conversationId);
        }

        async loadMessages(conversationId) {
            try {
                console.log(`Loading messages for conversation ${conversationId}`);
                
                const response = await fetch(`get-messages.php?conversation_id=${conversationId}`, {
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Messages data:', data);
                
                const container = document.getElementById('messages-display');
                if (!container) return;
                
                container.innerHTML = '';
                
                if (data.success) {
                    if (data.messages.length > 0) {
                        this.lastMessageId = data.messages[data.messages.length - 1].id;
                        console.log(`Last message ID set to: ${this.lastMessageId}`);
                    }
                    
                    data.messages.forEach(msg => {
                        this.appendMessageToContainer(msg, container);
                    });
                    
                    container.scrollTop = container.scrollHeight;
                    
                    await fetch(`mark-messages-read.php?conversation_id=${conversationId}`, {
                        credentials: 'include'
                    });
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                showNotification('error', 'Erreur de chargement des messages');
            }
        }

        appendMessageToContainer(msg, container) {
            const messageElement = document.createElement('div');
            messageElement.className = `message-item ${
                msg.sender_id == this.currentUserId ? 'message-sender' : 'message-receiver'
            }`;
            
            const msgDate = new Date(msg.sent_at);
            const formattedTime = msgDate.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            messageElement.innerHTML = `
                <div class="message-content">
                    <p>${this.escapeHtml(msg.content).replace(/\n/g, '<br>')}</p>
                    <div class="message-meta">
                        ${formattedTime} 
                        ${msg.is_read ? '✓✓' : '✓'}
                    </div>
                </div>
            `;
            
            container.appendChild(messageElement);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async sendMessage() {
            const input = document.getElementById('message-input');
            if (!input) return;
            
            const message = input.value.trim();
            
            if (!message || !this.currentConversationId) {
                showNotification('error', 'Veuillez écrire un message');
                return;
            }
            
            try {
                console.log(`Sending message to conversation ${this.currentConversationId}`);
                
                const formData = new FormData();
                formData.append('conversation_id', this.currentConversationId);
                formData.append('content', message);
                
                const response = await fetch('send-message.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Send message response:', data);
                
                if (data.success) {
                    input.value = '';
                    if (data.message_id) {
                        this.lastMessageId = data.message_id;
                    }
                    await this.loadMessages(this.currentConversationId);
                    this.updateConversationList();
                } else {
                    showNotification('error', data.message || 'Erreur lors de l\'envoi');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showNotification('error', 'Erreur réseau lors de l\'envoi');
            }
        }

        async openNewConversationModal() {
            console.log('Opening new conversation modal...');
            const messagesModal = document.getElementById('messages-modal');
            if (messagesModal) {
                messagesModal.style.display = 'none';
            }
            
            const modal = document.getElementById('new-message-modal');
            if (modal) {
                modal.style.display = 'block';
            }
            
            await this.loadUsersForNewConversation();
        }

        async loadUsersForNewConversation() {
            console.log('Loading users for new conversation...');
            const selectElement = document.getElementById('recipient-select');
            const loadingIndicator = document.getElementById('recipient-loading');
            
            if (!selectElement || !loadingIndicator) {
                console.error('Required elements for new conversation not found');
                return;
            }
            
            try {
                selectElement.innerHTML = '<option value="">Chargement des contacts...</option>';
                loadingIndicator.style.display = 'block';
                
                const response = await fetch('get-users.php', {
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`Erreur ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Users data:', data);
                
                if (!data.success) {
                    throw new Error(data.message || 'Erreur inconnue');
                }
                
                selectElement.innerHTML = '<option value="">Sélectionnez un destinataire</option>';
                
                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        const option = new Option(user.name, user.id);
                        selectElement.add(option);
                    });
                    console.log(`${data.users.length} contacts chargés`);
                } else {
                    const errorMsg = 'Aucun contact disponible';
                    console.warn(errorMsg);
                    selectElement.innerHTML += `<option value="" disabled>${errorMsg}</option>`;
                }
            } catch (error) {
                console.error('Error loading users:', error);
                selectElement.innerHTML = '<option value="" disabled>Erreur de chargement</option>';
                showNotification('error', 'Impossible de charger les contacts');
                
                setTimeout(() => {
                    console.log('Tentative de rechargement des contacts...');
                    this.loadUsersForNewConversation();
                }, 5000);
            } finally {
                loadingIndicator.style.display = 'none';
            }
        }
        
        async startNewConversation() {
            console.log('Starting new conversation...');
            const recipientSelect = document.getElementById('recipient-select');
            const messageInput = document.getElementById('new-message-content');
            
            if (!recipientSelect || !messageInput) {
                console.error('Required elements not found');
                return;
            }
            
            const recipientId = recipientSelect.value;
            const message = messageInput.value.trim();
            
            if (!recipientId || !message) {
                showNotification('error', 'Veuillez sélectionner un destinataire et écrire un message');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('recipient_id', recipientId);
                formData.append('content', message);
                
                console.log(`Starting conversation with user ${recipientId}`);
                
                const response = await fetch('start-conversation.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Start conversation response:', data);
                
                if (data.success) {
                    const newMessageModal = document.getElementById('new-message-modal');
                    const messagesModal = document.getElementById('messages-modal');
                    
                    if (newMessageModal) newMessageModal.style.display = 'none';
                    if (messagesModal) messagesModal.style.display = 'block';
                    
                    const recipientName = recipientSelect.selectedOptions[0].textContent.trim();
                    
                    this.openConversation(data.conversation_id, recipientId, recipientName);
                    this.updateConversationList();
                } else {
                    showNotification('error', data.message || 'Erreur lors de la création');
                }
            } catch (error) {
                console.error('Error starting conversation:', error);
                showNotification('error', 'Erreur réseau lors de la création');
            }
        }

        updateUnreadBadge(count) {
            console.log(`Updating unread badge with count: ${count}`);
            const badge = document.getElementById('total-unread-count');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        formatDate(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            const now = new Date();
            
            if (date.toDateString() === now.toDateString()) {
                return date.toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else if (date.getFullYear() === now.getFullYear()) {
                return date.toLocaleDateString('fr-FR', {
                    month: 'short',
                    day: 'numeric'
                });
            } else {
                return date.toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }
    }

    console.log('Creating new MessagingSystem instance...');
    window.messagingSystem = new MessagingSystem();
});