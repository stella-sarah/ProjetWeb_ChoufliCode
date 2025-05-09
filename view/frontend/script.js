document.addEventListener('DOMContentLoaded', function() {
    const messagingSystem = new MessagingSystem();
    
    // Make it available globally if needed
    window.messagingSystem = messagingSystem;

    const chatbotToggle = document.querySelector('.chatbot-toggle');
    const chatbotContainer = document.querySelector('.chatbot-container');
    const closeChatbot = document.querySelector('.close-chatbot');

    const quickSuggestions = document.querySelectorAll('.quick-suggestion');
    const suggestionMessages = {
        'plages': "Quelles sont les plus belles plages en Tunisie?",
        'hotels': "Pouvez-vous recommander des hôtels de qualité?",
        'nourriture': "Quelles sont les spécialités culinaires tunisiennes?",
        'desert': "Je veux des infos sur les excursions dans le désert"
    };

    quickSuggestions.forEach(suggestion => {
        suggestion.addEventListener('click', function() {
            const queryType = this.dataset.query;
            const message = suggestionMessages[queryType];
            
            if (message) {
                this.style.transform = 'scale(0.9)';
                this.style.backgroundColor = 'rgba(201, 168, 108, 0.3)';
                
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                    this.style.backgroundColor = 'rgba(201, 168, 108, 0.1)';
                }, 200);
                
                chatbotInput.value = message;
                sendMessage();
            }
        });
    });
    
    chatbotToggle.addEventListener('click', function() {
        chatbotContainer.classList.toggle('active');
    });
    
    closeChatbot.addEventListener('click', function() {
        chatbotContainer.classList.remove('active');
    });
    
    const chatbotInput = document.querySelector('.chatbot-input input');
    const chatbotMessages = document.querySelector('.chatbot-messages');
    const chatbotSendBtn = document.querySelector('.chatbot-input button');
    
    function getBotResponse(message) {
        const lowerMsg = message.toLowerCase();
        
        const responses = {
            'bonjour': "Bonjour! Je suis votre guide virtuel pour découvrir la Tunisie. Comment puis-je vous aider aujourd'hui?",
            'salut': "Salut! Prêt(e) à explorer les merveilles de la Tunisie? Dites-moi ce qui vous intéresse!",
            'hello': "Hello! Je suis ravi de vous aider à planifier votre voyage en Tunisie. Par où commencer?",
            'conseil': "Voici mes recommandations pour votre voyage en Tunisie:\n\n" +
                      "1. Sidi Bou Said - Village emblématique aux maisons bleues et blanches\n" +
                      "2. Le désert du Sahara - Expérience inoubliable avec des nuits sous les étoiles\n" +
                      "3. Djerba - Île paradisiaque avec des plages de sable fin\n" +
                      "4. Le Colisée d'El Jem - Monument historique impressionnant\n\n" +
                      "Quel type d'expérience recherchez-vous exactement?",
            'recommande': "Je peux recommander plusieurs expériences uniques en Tunisie:\n\n" +
                         "• Dégustation de cuisine locale dans les souks\n" +
                         "• Randonnée dans les montagnes de Tabarka\n" +
                         "• Séjour dans un hôtel de luxe à Hammamet\n" +
                         "• Visite des sites archéologiques romains\n\n" +
                         "Quel est votre budget et vos centres d'intérêt?",
            'plage': "Les meilleures plages de Tunisie:\n\n" +
                    "🏖️ Hammamet - Plages familiales avec infrastructures modernes\n" +
                    "🏖️ Djerba - Eaux cristallines et sable blanc\n" +
                    "🏖️ Sousse - Plages animées avec activités nautiques\n" +
                    "🏖️ Tabarka - Côtes rocheuses idéales pour la plongée\n\n" +
                    "Quelle période de l'année prévoyez-vous votre visite?",
            'mer': "La Tunisie offre un littoral méditerranéen exceptionnel. Voici mes préférés:\n\n" +
                  "• Mahdia - Pour des eaux turquoises et une ambiance paisible\n" +
                  "• Bizerte - Pour combiner plage et culture\n" +
                  "• Zarzis - Pour des complexes hôteliers haut de gamme\n\n" +
                  "Cherchez-vous plutôt du calme ou de l'animation?",
            'désert': "Une expérience dans le désert tunisien est magique! Voici ce que je recommande:\n\n" +
                     "📍 Douz - La 'porte du désert', idéale pour les excursions\n" +
                     "📍 Tozeur - Oasis luxuriante et palmeraies\n" +
                     "📍 Ksar Ghilane - Sources chaudes en plein désert\n\n" +
                     "Préférez-vous une excursion d'une journée ou un séjour plus long?",
            'sahara': "Le Sahara tunisien offre des paysages à couper le souffle:\n\n" +
                     "• Randonnée à dos de chameau au coucher du soleil\n" +
                     "• Nuitée dans un campement bédouin traditionnel\n" +
                     "• Visite des décors de Star Wars à Tataouine\n\n" +
                     "Quelle durée prévoyez-vous pour votre aventure saharienne?",
            'nourriture': "La cuisine tunisienne est délicieuse! Voici ce qu'il faut absolument goûter:\n\n" +
                         "🍽️ Couscous - Le plat national sous diverses variantes\n" +
                         "🍽️ Brik - Feuilleté croustillant à l'oeuf et au thon\n" +
                         "🍽️ Lablabi - Soupe de pois chiches réconfortante\n" +
                         "🍽️ Makroudh - Pâtisserie à base de dattes\n\n" +
                         "Avez-vous des restrictions alimentaires?",
            'manger': "Pour une expérience culinaire authentique, je recommande:\n\n" +
                     "• Les restaurants populaires dans les médinas\n" +
                     "• Les gargotes locales pour des prix abordables\n" +
                     "• Les établissements haut de gamme pour une cuisine raffinée\n\n" +
                     "Quel type d'ambiance recherchez-vous?",
            'culture': "La Tunisie regorge de trésors culturels:\n\n" +
                      "🏛️ Le Musée du Bardo à Tunis - Collection de mosaïques romaines\n" +
                      "🕌 La Grande Mosquée de Kairouan - Site classé au patrimoine de l'UNESCO\n" +
                      "🎭 Le Festival international de Carthage - Événement culturel majeur\n\n" +
                      "Quelle période de l'histoire vous intéresse particulièrement?",
            'hôtel': "Pour choisir votre hébergement en Tunisie:\n\n" +
                    "⭐ 5 étoiles - Luxe et service impeccable (ex: The Residence)\n" +
                    "⭐ 4 étoiles - Confort à prix raisonnable\n" +
                    "⭐ Hôtels de charme - Authenticité et caractère\n" +
                    "⭐ Auberges - Pour les voyageurs en quête de rencontres\n\n" +
                    "Dans quelle région cherchez-vous à loger?",
            'transport': "Se déplacer en Tunisie:\n\n" +
                        "🚗 Location de voiture - Pour une grande liberté\n" +
                        "🚆 Train - Confortable entre les grandes villes\n" +
                        "🚌 Bus - Réseau étendu et économique\n" +
                        "✈️ Vols intérieurs - Pour gagner du temps\n\n" +
                        "Quel est votre itinéraire prévu?",
            'merci': "Avec plaisir! N'hésitez pas si vous avez d'autres questions sur la Tunisie. Bon voyage! 😊",
            'aide': "Je suis là pour vous aider à découvrir les merveilles de la Tunisie. Voici ce que je peux faire:\n\n" +
                   "• Donner des conseils sur les destinations\n" +
                   "• Recommander des activités selon vos goûts\n" +
                   "• Aider à planifier votre itinéraire\n" +
                   "• Fournir des infos pratiques (hébergement, transport...)\n\n" +
                   "Sur quel sujet souhaitez-vous en savoir plus?"
        };

        if (lowerMsg.includes('merci') || lowerMsg.includes('parfait')) {
            return responses['merci'];
        } else if (lowerMsg.includes('aide') || lowerMsg.includes('support')) {
            return responses['aide'];
        } else if (lowerMsg.includes('conseil') || lowerMsg.includes('recommande') || lowerMsg.includes('suggère')) {
            return responses['conseil'];
        } else if (lowerMsg.includes('plage') || lowerMsg.includes('mer')) {
            return responses['plage'];
        } else if (lowerMsg.includes('désert') || lowerMsg.includes('sahara')) {
            return responses['désert'];
        } else if (lowerMsg.includes('nourriture') || lowerMsg.includes('manger') || lowerMsg.includes('cuisine')) {
            return responses['nourriture'];
        } else if (lowerMsg.includes('culture') || lowerMsg.includes('histoire') || lowerMsg.includes('patrimoine')) {
            return responses['culture'];
        } else if (lowerMsg.includes('hôtel') || lowerMsg.includes('hébergement') || lowerMsg.includes('logement')) {
            return responses['hôtel'];
        } else if (lowerMsg.includes('transport') || lowerMsg.includes('se déplacer') || lowerMsg.includes('bus') || lowerMsg.includes('train')) {
            return responses['transport'];
        } else if (lowerMsg.includes('bonjour') || lowerMsg.includes('salut') || lowerMsg.includes('hello')) {
            return responses['bonjour'];
        } else {
            return "Je suis ravi de vous aider à découvrir la Tunisie! Voici quelques sujets sur lesquels je peux vous informer:\n\n" +
                   "• Les meilleures destinations selon vos goûts\n" +
                   "• Les périodes idéales pour visiter\n" +
                   "• Les spécialités culinaires à ne pas manquer\n" +
                   "• Les activités culturelles et aventures\n\n" +
                   "Dites-moi ce qui vous intéresse et je vous guiderai au mieux!";
        }
    }

    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            const userMessage = document.createElement('div');
            userMessage.classList.add('message', 'user-message');
            
            const formattedMessage = message.replace(/\n/g, '<br>');
            userMessage.innerHTML = formattedMessage;
            
            chatbotMessages.appendChild(userMessage);
            
            const typingIndicator = document.createElement('div');
            typingIndicator.classList.add('message', 'typing-indicator');
            typingIndicator.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
            chatbotMessages.appendChild(typingIndicator);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            
            const typingTime = Math.min(Math.max(message.length * 50, 1000), 3000);
            
            setTimeout(() => {
                typingIndicator.remove();
                
                const botMessage = document.createElement('div');
                botMessage.classList.add('message', 'bot-message');
                
                const botResponse = getBotResponse(message);
                const formattedResponse = botResponse.replace(/\n/g, '<br>');
                
                botMessage.innerHTML = formattedResponse;
                chatbotMessages.appendChild(botMessage);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                
                saveConversation(message, botResponse);
            }, typingTime);
            
            chatbotInput.value = '';
        }
    }

    function saveConversation(userMessage, botResponse) {
        let conversations = JSON.parse(localStorage.getItem('chatbotConversations')) || [];
        
        if (conversations.length >= 50) {
            conversations.shift();
        }
        
        conversations.push({
            user: userMessage,
            bot: botResponse,
            timestamp: new Date().toISOString()
        });
        
        localStorage.setItem('chatbotConversations', JSON.stringify(conversations));
    }

    function loadConversationHistory() {
        const conversations = JSON.parse(localStorage.getItem('chatbotConversations')) || [];
        const lastConversations = conversations.slice(-3);
        
        lastConversations.forEach(conv => {
            const userMsg = document.createElement('div');
            userMsg.classList.add('message', 'user-message');
            userMsg.textContent = conv.user;
            chatbotMessages.appendChild(userMsg);
            
            const botMsg = document.createElement('div');
            botMsg.classList.add('message', 'bot-message');
            botMsg.textContent = conv.bot;
            chatbotMessages.appendChild(botMsg);
        });
        
        if (lastConversations.length > 0) {
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
    }

    loadConversationHistory();
    
    chatbotSendBtn.addEventListener('click', sendMessage);
    
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    const emojiToggle = document.getElementById('emoji-toggle');
    const emojiList = document.getElementById('emoji-list');
    const newPostTextarea = document.getElementById('new-post-content');

    if (emojiToggle && emojiList && newPostTextarea) {
        emojiToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            emojiList.classList.toggle('hidden');
        });

        emojiList.querySelectorAll('span').forEach(emoji => {
            emoji.addEventListener('click', function() {
                const emojiText = this.textContent;
                const emojiChar = String.fromCodePoint(parseInt([...emojiText].map(c => c.codePointAt(0).toString(16)).join('-').split('-')[0], 16));
                newPostTextarea.value += emojiChar;
                newPostTextarea.focus();
                emojiList.classList.add('hidden');
            });
        });

        document.addEventListener('click', function() {
            emojiList.classList.add('hidden');
        });

        emojiList.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    const imageUpload = document.getElementById('image-upload');
    const videoUpload = document.getElementById('video-upload');
    const mediaPreview = document.getElementById('media-preview');

    if (imageUpload && videoUpload && mediaPreview) {
        imageUpload.addEventListener('change', function(e) {
            handleMediaUpload(e.target.files[0], 'image');
        });

        videoUpload.addEventListener('change', function(e) {
            handleMediaUpload(e.target.files[0], 'video');
        });

        function handleMediaUpload(file, type) {
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const mediaElement = type === 'image' 
                    ? document.createElement('img') 
                    : document.createElement('video');
                
                mediaElement.src = e.target.result;
                mediaElement.controls = type === 'video';
                mediaElement.classList.add('uploaded-media');
                
                mediaPreview.innerHTML = '';
                mediaPreview.appendChild(mediaElement);
            };
            reader.readAsDataURL(file);
        }
    }

    loadPosts();
    loadAnnouncements();

    const frenchBadWords = ['merde', 'putain', 'connard', 'salope'];
    const englishBadWords = ['shit', 'fuck', 'asshole', 'bitch'];
    const arabicBadWords = ['كلب', 'حقير', 'غبي', 'سخيف'];
    const allBadWords = [...frenchBadWords, ...englishBadWords, ...arabicBadWords];

    function containsBadWords(text) {
        const regex = new RegExp(`\\b(${allBadWords.join('|')})\\b`, 'i');
        return regex.test(text);
    }

    const postForm = document.getElementById('post-form');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('post-title').value.trim();
            const content = document.getElementById('new-post-content').value.trim();
            const hasImage = imageUpload.files.length > 0;
            const hasVideo = videoUpload.files.length > 0;
            
            if (!title) {
                showNotification('error', 'Veuillez ajouter un titre à votre publication.');
                return;
            }
            
            if (!content && !hasImage && !hasVideo) {
                showNotification('error', 'Veuillez écrire quelque chose ou ajouter une image/vidéo.');
                return;
            }

            if (containsBadWords(title) || containsBadWords(content)) {
                showNotification('error', 'Veuillez éviter l\'utilisation de langage inapproprié.');
                return;
            }

            const formData = new FormData(postForm);

            fetch('add-post.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const postElement = createPostElement(data.post);
                    document.getElementById('posts-container').prepend(postElement);
                    postForm.reset();
                    mediaPreview.innerHTML = '';
                    showNotification('success', 'Publication ajoutée avec succès !');
                } else {
                    showNotification('error', 'Erreur: ' + data.message);
                }
            })
            .catch(error => {
                showNotification('error', 'Erreur réseau: ' + error);
            });
        });
    }

    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const searchType = document.getElementById('search-type');
    
    searchBtn.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    function performSearch() {
        const query = searchInput.value.trim();
        const type = searchType.value;
        
        if (query.length === 0) {
            loadPosts();
            return;
        }
        
        fetch(`search-posts.php?query=${encodeURIComponent(query)}&type=${type}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById("posts-container");
                if (!container) return;

                if (data.success && data.posts && data.posts.length > 0) {
                    container.innerHTML = '';
                    data.posts.forEach(post => {
                        const postElement = createPostElement(post);
                        container.appendChild(postElement);
                    });
                    highlightSearchResults(query);
                } else {
                    container.innerHTML = '<p class="no-results">Aucun résultat trouvé pour votre recherche.</p>';
                }
            })
            .catch(error => {
                showNotification('error', 'Erreur de recherche: ' + error);
            });
    }
    
    function highlightSearchResults(query) {
        const posts = document.querySelectorAll('.post-content p');
        const regex = new RegExp(query, 'gi');
        
        posts.forEach(post => {
            const text = post.textContent;
            const highlightedText = text.replace(regex, match => 
                `<span class="highlight">${match}</span>`
            );
            post.innerHTML = highlightedText;
        });
    }

    document.querySelector('.close-modal').addEventListener('click', closeReportModal);

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('report-modal');
        if (event.target === modal) {
            closeReportModal();
        }
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    applyFullWidthStyles();
});

class MessagingSystem {
    constructor() {
        this.currentUserId = null;
        this.currentConversationId = null;
        this.currentRecipientId = null;
        this.currentRecipientName = null;
        this.typingTimeout = null;
        this.initialize();
    }

    async initialize() {
        await this.loadCurrentUser();
        this.initEventListeners();
        this.setupAutoRefresh();
    }

    async loadCurrentUser() {
        try {
            const response = await fetch('get-current-user.php', {
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                this.currentUserId = data.user.id;
                document.querySelectorAll('.user-name').forEach(el => {
                    el.textContent = data.user.name;
                });
            } else {
                console.error('Failed to load user:', data.message);
            }
        } catch (error) {
            console.error('Error loading current user:', error);
        }
    }

    initEventListeners() {
        document.getElementById('messages-link').addEventListener('click', (e) => {
            e.preventDefault();
            this.openMessagingModal();
        });

        document.getElementById('new-conversation-btn').addEventListener('click', () => {
            this.openNewConversationModal();
        });

        document.getElementById('send-message-btn').addEventListener('click', () => {
            this.sendMessage();
        });

        document.getElementById('start-conversation-btn').addEventListener('click', () => {
            this.startNewConversation();
        });

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });

        document.getElementById('message-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        document.getElementById('message-input').addEventListener('input', () => {
            this.sendTypingIndicator();
        });
    }

    setupAutoRefresh() {
        setInterval(() => {
            if (this.currentConversationId) {
                this.loadMessages(this.currentConversationId);
            }
            this.loadConversations();
        }, 5000);
    }

    async openMessagingModal() {
        const modal = document.getElementById('messages-modal');
        modal.style.display = 'block';
        
        document.getElementById('conversations').innerHTML = '<div class="loading">Chargement...</div>';
        
        await this.loadConversations();
    }

    async loadConversations() {
        const container = document.getElementById('conversations');
        if (!container) {
            console.error('Conteneur des conversations introuvable');
            return;
        }
    
        try {
            container.innerHTML = '<div class="loading">Chargement...</div>';
            
            const response = await fetch('get-conversations.php', {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
    
            // Vérifier le type de contenu
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const errorText = await response.text();
                console.error('Réponse non-JSON:', errorText);
                throw new Error('Le serveur a renvoyé une réponse inattendue');
            }
    
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Erreur lors du chargement');
            }
    
            container.innerHTML = '';
    
            if (!data.conversations || data.conversations.length === 0) {
                container.innerHTML = '<p class="no-conversations">Aucune conversation</p>';
                return;
            }
    
            // Traitement des conversations...
            data.conversations.forEach(conv => {
                const convElement = document.createElement('div');
                convElement.className = 'conversation';
                convElement.innerHTML = `
                    <div class="conversation-header">
                        <strong>${conv.recipient_name}</strong>
                        <span class="conversation-date">
                            ${conv.last_message_time ? new Date(conv.last_message_time).toLocaleString() : ''}
                        </span>
                    </div>
                    <p class="conversation-preview">${conv.last_message || 'Aucun message'}</p>
                    ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
                `;
                container.appendChild(convElement);
            });
    
        } catch (error) {
            console.error('Erreur:', error);
            container.innerHTML = `
                <div class="error-message">
                    <p>Erreur de chargement</p>
                    <button onclick="window.messagingSystem.loadConversations()">
                        Réessayer
                    </button>
                    <p class="error-detail">${error.message}</p>
                </div>
            `;
        }
    }

    updateUnreadBadge(count) {
        const badge = document.getElementById('total-unread-count');
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    }

    async openConversation(conversationId, recipientId, recipientName) {
        this.currentConversationId = conversationId;
        this.currentRecipientId = recipientId;
        this.currentRecipientName = recipientName;
        
        document.getElementById('conversation-with').textContent = `Conversation avec ${recipientName}`;
        
        document.querySelectorAll('.conversation').forEach(c => {
            c.classList.remove('active');
        });
        document.querySelector(`.conversation[data-conversation-id="${conversationId}"]`).classList.add('active');
        
        await this.loadMessages(conversationId);
        
        const messagesDisplay = document.getElementById('messages-display');
        messagesDisplay.scrollTop = messagesDisplay.scrollHeight;
    }

    async loadMessages(conversationId) {
        try {
            const response = await fetch(`get-messages.php?conversation_id=${conversationId}`, {
                credentials: 'include'
            });
            const data = await response.json();
            
            if (data.success) {
                const container = document.getElementById('messages-display');
                container.innerHTML = '';
                
                data.messages.forEach(msg => {
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
                            <p>${msg.content.replace(/\n/g, '<br>')}</p>
                            <div class="message-meta">${formattedTime} ${msg.is_read ? '✓✓' : '✓'}</div>
                        </div>
                    `;
                    container.appendChild(messageElement);
                });
                
                container.scrollTop = container.scrollHeight;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            showNotification('error', 'Erreur lors du chargement des messages');
        }
    }

    async sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        
        if (!message || !this.currentConversationId) {
            showNotification('error', 'Veuillez écrire un message');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('conversation_id', this.currentConversationId);
            formData.append('content', message);
            
            const response = await fetch('send-message.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                input.value = '';
                await this.loadMessages(this.currentConversationId);
                await this.loadConversations();
            } else {
                showNotification('error', data.message || 'Erreur lors de l\'envoi');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showNotification('error', 'Erreur réseau lors de l\'envoi');
        }
    }

    async sendTypingIndicator() {
        if (!this.currentConversationId) return;
        
        if (this.typingTimeout) {
            clearTimeout(this.typingTimeout);
        }
        
        const container = document.getElementById('typing-indicator-container');
        container.innerHTML = `
            <div class="typing-indicator">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        
        this.typingTimeout = setTimeout(() => {
            container.innerHTML = '';
        }, 3000);
    }

    async openNewConversationModal() {
        document.getElementById('messages-modal').style.display = 'none';
        const modal = document.getElementById('new-message-modal');
        modal.style.display = 'block';
        
        await this.loadUsersForNewConversation();
    }

    async loadUsersForNewConversation() {
        const selectElement = document.getElementById('recipient-select');
        const loadingIndicator = document.getElementById('recipient-loading');
        
        try {
            // Réinitialiser et afficher le chargement
            selectElement.innerHTML = '<option value="">Chargement des contacts...</option>';
            loadingIndicator.style.display = 'block';
    
            const response = await fetch('get-users.php', {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            });
    
            // Vérification cruciale de la réponse
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Erreur serveur: ${response.status} - ${errorText}`);
            }
    
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Réponse non-JSON reçue du serveur');
            }
    
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Le serveur a retourné une erreur');
            }
    
            // Mise à jour de l'interface
            selectElement.innerHTML = '<option value="">Sélectionnez un destinataire</option>';
    
            if (data.users && data.users.length > 0) {
                data.users.forEach(user => {
                    const option = new Option(
                        user.name || user.email, // Texte affiché
                        user.id,                // Valeur
                        false,                 // selected
                        false                  // disabled
                    );
                    selectElement.add(option);
                });
            } else {
                selectElement.innerHTML += '<option value="" disabled>Aucun contact disponible</option>';
                showNotification('info', 'Aucun autre utilisateur trouvé');
            }
    
        } catch (error) {
            console.error('Échec du chargement des utilisateurs:', error);
            
            selectElement.innerHTML = `
                <option value="" disabled>Erreur de chargement</option>
                <option value="retry" onclick="window.messagingSystem.loadUsersForNewConversation()">
                    Réessayer - ${error.message}
                </option>
            `;
            
            showNotification('error', `Échec du chargement: ${error.message}`);
        } finally {
            loadingIndicator.style.display = 'none';
        }
    }
        
    async startNewConversation() {
        const recipientId = document.getElementById('recipient-select').value;
        const message = document.getElementById('new-message-content').value.trim();
        
        if (!recipientId || !message) {
            showNotification('error', 'Veuillez sélectionner un destinataire et écrire un message');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('recipient_id', recipientId);
            formData.append('content', message);
            
            const response = await fetch('start-conversation.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification('success', 'Conversation démarrée avec succès');
                
                document.getElementById('new-message-modal').style.display = 'none';
                document.getElementById('messages-modal').style.display = 'block';
                
                const recipientName = document.getElementById('recipient-select')
                    .selectedOptions[0].textContent.trim();
                
                this.openConversation(data.conversation_id, recipientId, recipientName);
                this.loadConversations();
            } else {
                showNotification('error', data.message || 'Erreur lors de la création');
            }
        } catch (error) {
            console.error('Error starting conversation:', error);
            showNotification('error', 'Erreur réseau lors de la création');
        }
    }
}

async function updatePost(postId, newTitle, newContent) {
    try {
        const response = await fetch('update-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}&title=${encodeURIComponent(newTitle)}&content=${encodeURIComponent(newContent)}`
        });

        const data = await response.json();

        if (data.success) {
            loadPosts();
            showNotification('success', 'Post mis à jour avec succès !');
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour: ' + error.message);
    }
}

async function updateComment(commentId, newContent) {
    try {
        const response = await fetch('update-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}&content=${encodeURIComponent(newContent)}`
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erreur serveur');
        }

        if (!data.success) {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }

        showNotification('success', 'Commentaire mis à jour avec succès !');
        return data;
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur: ' + error.message);
        throw error;
    }
}

function showNotification(type, message) {
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    let icon;
    switch(type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-exclamation-circle"></i>';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
            type = 'info';
    }
    
    notification.innerHTML = `${icon} ${message}`;
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
            if (container && container.children.length === 0) {
                container.remove();
            }
        }, 500);
    }, 5000);
    
    notification.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
            if (container && container.children.length === 0) {
                container.remove();
            }
        }, 500);
    });
}

function loadPosts() {
    fetch("get-posts.php")
        .then(res => {
            if (!res.ok) throw new Error('Erreur réseau');
            return res.json();
        })
        .then(data => {
            const container = document.getElementById("posts-container");
            if (!container) return;

            if (data.success && data.posts && data.posts.length > 0) {
                container.innerHTML = '';
                data.posts.forEach(post => {
                    const postElement = createPostElement(post);
                    container.appendChild(postElement);
                });
            }
        })
        .catch(error => {
            showNotification('error', 'Erreur de chargement des posts: ' + error);
        });
}

function createPostElement(post) {
    const article = document.createElement("article");
    article.className = "post";
    article.dataset.postId = post.id;

    const likes = JSON.parse(localStorage.getItem('postLikes')) || {};
    const isLiked = likes[post.id] || false;
    const likeCount = isLiked ? 1 : 0;

    const initials = post.author ? post.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON';
    
    const contentWithEmojis = post.content.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
        match => `<span class="emoji">${match}</span>`);
    
    let commentsHTML = '';
    if (post.comments) {
        commentsHTML = post.comments.map(comment => {
            const commentWithEmojis = comment.content.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                match => `<span class="emoji">${match}</span>`);
            return `
                <div class="comment" data-comment-id="${comment.id}">
                    <div class="default-avatar">${comment.author ? comment.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON'}</div>
                    <div class="comment-content">
                        <p class="author">${comment.author || 'Anonyme'} <span class="date">- ${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                        <p>${commentWithEmojis}</p>
                        <div class="comment-actions">
                            <button class="like-comment-btn" data-comment-id="${comment.id}">
                                <i class="far fa-heart"></i>
                                <span class="like-count">${comment.likes || 0}</span>
                            </button>
                            <button class="delete-comment-btn" data-comment-id="${comment.id}" title="Supprimer ce commentaire">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="edit-comment-btn" data-comment-id="${comment.id}" title="Modifier ce commentaire">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    article.innerHTML = `
        <div class="post-header">
            <div class="default-avatar">${initials}</div>
            <div class="post-meta">
                <h3>${post.title}</h3>
                ${post.author ? `<p class="author">Par <span>${post.author}</span></p>` : '<p class="author">Par <span>Anonyme</span></p>'}
                <p class="date">${new Date(post.created_at).toLocaleDateString('fr-FR')}</p>
            </div>
            <button class="delete-post-btn" title="Supprimer ce post">
                <i class="fas fa-trash"></i>
            </button>
            <button class="edit-post-btn" data-post-id="${post.id}" title="Modifier ce post">
                <i class="fas fa-edit"></i>
            </button>
        </div>
        <div class="post-content">
            <p>${contentWithEmojis}</p>
            ${post.image_url ? `<img src="${post.image_url}" alt="Image">` : ''}
            ${post.video_url ? `<video src="${post.video_url}" controls></video>` : ''}
        </div>
        <div class="post-actions">
            <button class="like-btn ${isLiked ? 'liked' : ''}">
                <i class="${isLiked ? 'fas' : 'far'} fa-heart"></i> 
                <span class="like-count">${likeCount}</span>
            </button>
            <button class="comment-btn"><i class="far fa-comment"></i> Commenter</button>
            <button class="report-btn" title="Signaler ce contenu"><i class="fas fa-flag"></i></button>
        </div>
        <div class="comments-section" style="display: none;">
            <div class="comment-form">
                <div class="textarea-container">
                    <textarea placeholder="Ajouter un commentaire..."></textarea>
                    <div class="textarea-icons">
                        <span class="comment-emoji-toggle" title="Emoji">😀</span>
                    </div>
                    <div class="emoji-list hidden">
                        <span>🌴</span><span>🏜️</span><span>🌅</span><span>🏖️</span>
                        <span>🍛</span><span>🥙</span><span>🍵</span><span>🍋</span>
                        <span>🏛️</span><span>🕌</span><span>🎭</span><span>🧵</span>
                        <span>🐪</span><span>🇹🇳</span><span>❤️</span><span>👍</span>
                        <span>😍</span><span>🤩</span><span>👏</span><span>🎉</span>
                    </div>
                </div>
                <button class="btn-gold">Publier</button>
            </div>
            <div class="comment-list">
                ${commentsHTML}
            </div>
        </div>
    `;

    const deleteBtn = article.querySelector('.delete-post-btn');
    deleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        this.style.transform = 'rotate(360deg) scale(1.5)';
        setTimeout(() => {
            this.style.transform = 'rotate(0) scale(1)';
            
            showConfirmation('Voulez-vous vraiment supprimer ce post ?', (confirmed) => {
                if (confirmed) {
                    const postId = this.closest('.post').dataset.postId;
                    deletePost(postId, article);
                }
            });
        }, 500);
    });

    const editPostBtn = article.querySelector('.edit-post-btn');
    if (editPostBtn) {
        editPostBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const postElement = this.closest('.post');
            const title = postElement.querySelector('h3').textContent;
            const content = postElement.querySelector('.post-content p').innerHTML;
            
            this.style.transform = 'rotate(360deg) scale(1.5)';
            setTimeout(() => {
                this.style.transform = 'rotate(0) scale(1)';
            }, 500);
            
            const editForm = document.createElement('div');
            editForm.innerHTML = `
                <div class="edit-form">
                    <input type="text" class="full-width" value="${title.replace(/"/g, '"')}">
                    <textarea class="full-width">${content.replace(/"/g, '"')}</textarea>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="save-btn">
                            <i class="fas fa-check"></i> Enregistrer
                        </button>
                    </div>
                </div>
            `;
            
            postElement.querySelector('.post-content').innerHTML = '';
            postElement.querySelector('.post-content').appendChild(editForm);
            
            editForm.querySelector('.cancel-btn').addEventListener('click', function() {
                editForm.classList.add('cancel-effect');
                setTimeout(() => {
                    loadPosts();
                }, 500);
            });
            
            editForm.querySelector('.save-btn').addEventListener('click', function() {
                const newTitle = editForm.querySelector('input').value.trim();
                const newContent = editForm.querySelector('textarea').value.trim();
                
                if (newTitle && newContent) {
                    this.classList.add('save-success');
                    setTimeout(() => {
                        updatePost(postId, newTitle, newContent);
                    }, 1000);
                } else {
                    showNotification('error', 'Le titre et le contenu ne peuvent pas être vides');
                }
            });
        });
    }

    const likeBtn = article.querySelector('.like-btn');
    likeBtn.addEventListener('click', function() {
        const postId = this.closest('.post').dataset.postId;
        const likeCount = this.querySelector('.like-count');
        const icon = this.querySelector('i');
        
        let likes = JSON.parse(localStorage.getItem('postLikes')) || {};
        const isLiked = likes[postId] || false;
        
        likes[postId] = !isLiked;
        localStorage.setItem('postLikes', JSON.stringify(likes));
        
        if (isLiked) {
            this.classList.remove('liked');
            icon.classList.replace('fas', 'far');
            likeCount.textContent = parseInt(likeCount.textContent) - 1;
        } else {
            this.classList.add('liked');
            icon.classList.replace('far', 'fas');
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        }
    });

    const commentBtn = article.querySelector('.comment-btn');
    const commentFormBtn = article.querySelector('.comment-form .btn-gold');

    commentBtn.addEventListener('click', function() {
        const commentsSection = this.closest('.post').querySelector('.comments-section');
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
    });

    commentFormBtn.addEventListener('click', function() {
        const commentInput = this.previousElementSibling.querySelector('textarea');
        const commentText = commentInput.value.trim();
        
        if (!commentText) {
            showNotification('error', 'Veuillez écrire un commentaire.');
            return;
        }

        const postId = this.closest('.post').dataset.postId;
        
        fetch('add-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}&content=${encodeURIComponent(commentText)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const commentList = article.querySelector('.comment-list');
                const commentElement = document.createElement('div');
                commentElement.classList.add('comment');
                commentElement.dataset.commentId = data.comment.id;
                const commentWithEmojis = data.comment.content.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                    match => `<span class="emoji">${match}</span>`);
                commentElement.innerHTML = `
                    <div class="default-avatar">${data.comment.author ? data.comment.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON'}</div>
                    <div class="comment-content">
                        <p class="author">${data.comment.author || 'Anonyme'} <span class="date">- ${new Date(data.comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                        <p>${commentWithEmojis}</p>
                        <div class="comment-actions">
                            <button class="like-comment-btn" data-comment-id="${data.comment.id}">
                                <i class="far fa-heart"></i>
                                <span class="like-count">${data.comment.likes || 0}</span>
                            </button>
                            <button class="delete-comment-btn" data-comment-id="${data.comment.id}" title="Supprimer ce commentaire">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="edit-comment-btn" data-comment-id="${data.comment.id}" title="Modifier ce commentaire">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                const deleteBtn = commentElement.querySelector('.delete-comment-btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        this.style.transform = 'rotate(360deg) scale(1.5)';
                        setTimeout(() => {
                            this.style.transform = 'rotate(0) scale(1)';
                            
                            showConfirmation('Voulez-vous vraiment supprimer ce commentaire ?', (confirmed) => {
                                if (confirmed) {
                                    deleteComment(data.comment.id, this.closest('.comment'));
                                }
                            });
                        }, 500);
                    });
                }

                const editBtn = commentElement.querySelector('.edit-comment-btn');
                if (editBtn) {
                    editBtn.addEventListener('click', function() {
                        const commentId = this.getAttribute('data-comment-id');
                        const commentElement = this.closest('.comment');
                        const contentElement = commentElement.querySelector('.comment-content p:not(.author)');
                        const content = contentElement.innerHTML;
                        
                        this.style.transform = 'rotate(360deg) scale(1.5)';
                        setTimeout(() => {
                            this.style.transform = 'rotate(0) scale(1)';
                        }, 500);
                        
                        const editForm = document.createElement('div');
                        editForm.innerHTML = `
                            <div class="edit-form">
                                <textarea class="full-width">${content.replace(/"/g, '"')}</textarea>
                                <div class="form-actions">
                                    <button type="button" class="cancel-btn">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                    <button type="button" class="save-btn">
                                        <i class="fas fa-check"></i> Enregistrer
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        contentElement.style.display = 'none';
                        commentElement.querySelector('.comment-content').insertBefore(editForm, contentElement.nextSibling);
                        
                        editForm.querySelector('.cancel-btn').addEventListener('click', function() {
                            editForm.classList.add('cancel-effect');
                            setTimeout(() => {
                                editForm.remove();
                                contentElement.style.display = 'block';
                            }, 500);
                        });
                        
                        editForm.querySelector('.save-btn').addEventListener('click', function() {
                            const newContent = editForm.querySelector('textarea').value.trim();
                            
                            if (newContent) {
                                this.classList.add('save-success');
                                setTimeout(() => {
                                    updateComment(commentId, newContent)
                                        .then(() => {
                                            const newContentWithEmojis = newContent.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                                                match => `<span class="emoji">${match}</span>`);
                                            contentElement.innerHTML = newContentWithEmojis;
                                            editForm.remove();
                                            contentElement.style.display = 'block';
                                        })
                                        .catch(error => {
                                            showNotification('error', 'Erreur: ' + error.message);
                                        });
                                }, 500);
                            } else {
                                showNotification('error', 'Le contenu ne peut pas être vide');
                            }
                        });
                    });
                }
                
                commentList.appendChild(commentElement);
                commentInput.value = '';
                showNotification('success', 'Commentaire ajouté avec succès !');
                
                setTimeout(() => {
                    setupCommentEmojiSelectors();
                    setupCommentLikes();
                }, 0);
            } else {
                showNotification('error', 'Erreur: ' + data.message);
            }
        })
        .catch(error => {
            showNotification('error', 'Erreur réseau: ' + error);
        });
    });

    article.querySelectorAll('.delete-comment-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            this.style.transform = 'rotate(360deg) scale(1.5)';
            setTimeout(() => {
                this.style.transform = 'rotate(0) scale(1)';
                
                const commentId = this.getAttribute('data-comment-id');
                if (!commentId) {
                    showNotification('error', 'ID de commentaire invalide');
                    return;
                }
                
                showConfirmation('Voulez-vous vraiment supprimer ce commentaire ?', (confirmed) => {
                    if (confirmed) {
                        deleteComment(commentId, this.closest('.comment'));
                    }
                });
            }, 500);
        });
    });

    article.querySelectorAll('.edit-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const commentElement = this.closest('.comment');
            const contentElement = commentElement.querySelector('.comment-content p:not(.author)');
            const content = contentElement.innerHTML;
            
            this.style.transform = 'rotate(360deg) scale(1.5)';
            setTimeout(() => {
                this.style.transform = 'rotate(0) scale(1)';
            }, 500);
            
            const editForm = document.createElement('div');
            editForm.innerHTML = `
                <div class="edit-form">
                    <textarea class="full-width">${content.replace(/"/g, '"')}</textarea>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="button" class="save-btn">
                            <i class="fas fa-check"></i> Enregistrer
                        </button>
                    </div>
                </div>
            `;
            
            contentElement.style.display = 'none';
            commentElement.querySelector('.comment-content').insertBefore(editForm, contentElement.nextSibling);
            
            editForm.querySelector('.cancel-btn').addEventListener('click', function() {
                editForm.classList.add('cancel-effect');
                setTimeout(() => {
                    editForm.remove();
                    contentElement.style.display = 'block';
                }, 500);
            });
            
            editForm.querySelector('.save-btn').addEventListener('click', function() {
                const newContent = editForm.querySelector('textarea').value.trim();
                
                if (newContent) {
                    this.classList.add('save-success');
                    setTimeout(() => {
                        updateComment(commentId, newContent)
                            .then(() => {
                                const newContentWithEmojis = newContent.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                                    match => `<span class="emoji">${match}</span>`);
                                contentElement.innerHTML = newContentWithEmojis;
                                editForm.remove();
                                contentElement.style.display = 'block';
                            })
                            .catch(error => {
                                showNotification('error', 'Erreur: ' + error.message);
                            });
                    }, 500);
                } else {
                    showNotification('error', 'Le contenu ne peut pas être vide');
                }
            });
        });
    });

    const reportBtn = article.querySelector('.report-btn');
    reportBtn.addEventListener('click', function() {
        const postId = parseInt(this.closest('.post').dataset.postId);
        if (isNaN(postId) || postId <= 0) {
            showNotification('error', 'Erreur: ID de post invalide');
            return;
        }
        openReportModal(postId);
    });

    setTimeout(() => {
        setupCommentEmojiSelectors();
        setupCommentLikes();
    }, 0);

    return article;
}

function setupCommentEmojiSelectors() {
    document.querySelectorAll('.comment-form').forEach(form => {
        const emojiToggle = form.querySelector('.comment-emoji-toggle');
        const emojiList = form.querySelector('.emoji-list');
        const textarea = form.querySelector('textarea');

        if (emojiToggle && emojiList && textarea) {
            emojiToggle.replaceWith(emojiToggle.cloneNode(true));
            emojiList.replaceWith(emojiList.cloneNode(true));
            
            const newEmojiToggle = form.querySelector('.comment-emoji-toggle');
            const newEmojiList = form.querySelector('.emoji-list');

            newEmojiToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                newEmojiList.classList.toggle('hidden');
            });

            newEmojiList.querySelectorAll('span').forEach(emoji => {
                emoji.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    const emojiText = this.textContent;
                    const startPos = textarea.selectionStart;
                    const endPos = textarea.selectionEnd;
                    
                    textarea.value = textarea.value.substring(0, startPos) + 
                                   emojiText + 
                                   textarea.value.substring(endPos);
                    
                    textarea.selectionStart = textarea.selectionEnd = startPos + emojiText.length;
                    
                    textarea.focus();
                    newEmojiList.classList.add('hidden');
                }, { once: true });
            });

            document.addEventListener('click', function(e) {
                if (!newEmojiList.contains(e.target) && e.target !== newEmojiToggle) {
                    newEmojiList.classList.add('hidden');
                }
            }, { capture: true });
        }
    });
}

function setupCommentLikes() {
    document.querySelectorAll('.like-comment-btn:not(.initialized)').forEach(btn => {
        btn.classList.add('initialized');
        
        const commentId = btn.getAttribute('data-comment-id');
        let likedComments = JSON.parse(localStorage.getItem('likedComments')) || [];

        const isLiked = likedComments.includes(commentId);
        if (isLiked) {
            btn.classList.add('liked');
            btn.querySelector('i').classList.replace('far', 'fas');
        }

        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const likeCountElement = this.querySelector('.like-count');
            const currentLikes = parseInt(likeCountElement.textContent);
            const isCurrentlyLiked = this.classList.contains('liked');
            const newLikeStatus = !isCurrentlyLiked;

            try {
                const response = await fetch('like-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${commentId}&action=${newLikeStatus ? 'like' : 'unlike'}`
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Erreur inconnue');
                }

                likeCountElement.textContent = data.like_count;

                if (newLikeStatus) {
                    this.classList.add('liked');
                    this.querySelector('i').classList.replace('far', 'fas');
                    likedComments.push(commentId);
                } else {
                    this.classList.remove('liked');
                    this.querySelector('i').classList.replace('fas', 'far');
                    likedComments = likedComments.filter(id => id !== commentId);
                }

                localStorage.setItem('likedComments', JSON.stringify(likedComments));

            } catch (error) {
                console.error('Erreur:', error);
                showNotification('error', "Erreur lors de la mise à jour du like: " + error.message);
            }
        });
    });
}

async function deletePost(postId, postElement) {
    postId = parseInt(postId);
    if (isNaN(postId)) {
        showNotification('error', 'ID de post invalide');
        return;
    }

    try {
        const response = await fetch('delete-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}`
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erreur serveur');
        }

        if (data.success) {
            postElement.remove();
            showNotification('success', 'Post supprimé avec succès');
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression: ' + error.message);
    }
}

async function deleteComment(commentId, commentElement) {
    commentId = parseInt(commentId);
    if (isNaN(commentId)) {
        showNotification('error', 'ID de commentaire invalide');
        return;
    }

    try {
        const response = await fetch('delete-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Erreur serveur');
        }

        if (data.success) {
            commentElement.remove();
            showNotification('success', 'Commentaire supprimé avec succès');
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('error', error.message);
        
        const postElement = commentElement.closest('.post');
        if (postElement) {
            const postId = postElement.dataset.postId;
            loadComments(postId, postElement);
        }
    }
}

function loadComments(postId, postElement) {
    fetch(`get-comments.php?post_id=${postId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.comments) {
                const commentList = postElement.querySelector('.comment-list');
                commentList.innerHTML = '';
                
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.classList.add('comment');
                    commentElement.dataset.commentId = comment.id;
                    const initials = comment.author ? comment.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON';
                    const commentWithEmojis = comment.content.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                        match => `<span class="emoji">${match}</span>`);
                    
                    commentElement.innerHTML = `
                        <div class="default-avatar">${initials}</div>
                        <div class="comment-content">
                            <p class="author">${comment.author || 'Anonyme'} <span class="date">- ${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                            <p>${commentWithEmojis}</p>
                            <div class="comment-actions">
                                <button class="like-comment-btn" data-comment-id="${comment.id}">
                                    <i class="far fa-heart"></i>
                                    <span class="like-count">${comment.likes || 0}</span>
                                </button>
                                <button class="delete-comment-btn" data-comment-id="${comment.id}" title="Supprimer ce commentaire">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="edit-comment-btn" data-comment-id="${comment.id}" title="Modifier ce commentaire">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    const deleteBtn = commentElement.querySelector('.delete-comment-btn');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            this.style.transform = 'rotate(360deg) scale(1.5)';
                            setTimeout(() => {
                                this.style.transform = 'rotate(0) scale(1)';
                                
                                showConfirmation('Voulez-vous vraiment supprimer ce commentaire ?', (confirmed) => {
                                    if (confirmed) {
                                        deleteComment(comment.id, this.closest('.comment'));
                                    }
                                });                                
                            }, 500);
                        });
                    }

                    const editBtn = commentElement.querySelector('.edit-comment-btn');
                    if (editBtn) {
                        editBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            const commentElement = this.closest('.comment');
                            const contentElement = commentElement.querySelector('.comment-content p:not(.author)');
                            const content = contentElement.innerHTML;
                            
                            this.style.transform = 'rotate(360deg) scale(1.5)';
                            setTimeout(() => {
                                this.style.transform = 'rotate(0) scale(1)';
                            }, 500);
                            
                            const editForm = document.createElement('div');
                            editForm.innerHTML = `
                                <div class="edit-form">
                                    <textarea class="full-width">${content.replace(/"/g, '"')}</textarea>
                                    <div class="form-actions">
                                        <button type="button" class="cancel-btn">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                        <button type="button" class="save-btn">
                                            <i class="fas fa-check"></i> Enregistrer
                                        </button>
                                    </div>
                                </div>
                            `;
                            
                            contentElement.style.display = 'none';
                            commentElement.querySelector('.comment-content').insertBefore(editForm, contentElement.nextSibling);
                            
                            editForm.querySelector('.cancel-btn').addEventListener('click', function() {
                                editForm.classList.add('cancel-effect');
                                setTimeout(() => {
                                    editForm.remove();
                                    contentElement.style.display = 'block';
                                }, 500);
                            });
                            
                            editForm.querySelector('.save-btn').addEventListener('click', function() {
                                const newContent = editForm.querySelector('textarea').value.trim();
                                
                                if (newContent) {
                                    this.classList.add('save-success');
                                    setTimeout(() => {
                                        updateComment(commentId, newContent)
                                            .then(() => {
                                                const newContentWithEmojis = newContent.replace(/[\u{1F600}-\u{1F6FF}]/gu, 
                                                    match => `<span class="emoji">${match}</span>`);
                                                contentElement.innerHTML = newContentWithEmojis;
                                                editForm.remove();
                                                contentElement.style.display = 'block';
                                            })
                                            .catch(error => {
                                                showNotification('error', 'Erreur: ' + error.message);
                                            });
                                    }, 500);
                                } else {
                                    showNotification('error', 'Le contenu ne peut pas être vide');
                                }
                            });
                        });
                    }
                    
                    commentList.appendChild(commentElement);
                });

                setupCommentLikes();
            }
        })
        .catch(error => showNotification('error', 'Erreur: ' + error));
}

function openReportModal(postId) {
    const modal = document.getElementById('report-modal');
    const postIdInput = document.getElementById('reported-post-id');
    
    postId = parseInt(postId);
    if (isNaN(postId) || postId <= 0) {
        showNotification('error', 'Erreur: ID de post invalide');
        return;
    }
    
    postIdInput.value = postId;
    modal.style.display = 'block';
}

function closeReportModal() {
    document.getElementById('report-modal').style.display = 'none';
    document.getElementById('report-form').reset();
}

document.getElementById('report-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        const postId = parseInt(formData.get('post_id'));
        const reason = formData.get('reason');

        if (isNaN(postId) || postId <= 0) {
            throw new Error('ID de post invalide');
        }
        if (!reason) {
            throw new Error('Veuillez sélectionner une raison');
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        const response = await fetch('report-content.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Erreur HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erreur inconnue du serveur');
        }

        const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
        if (postElement) {
            postElement.remove();
        }
        
        showNotification('success', 'Signalement envoyé et post supprimé avec succès');
        closeReportModal();
        
    } catch (error) {
        console.error('Erreur de signalement:', error);
        
        if (error.name === 'AbortError') {
            showNotification('error', 'Erreur: Le serveur ne répond pas (timeout)');
        } else if (error.message.includes('Failed to fetch')) {
            showNotification('error', 'Erreur: Problème de connexion internet');
        } else {
            showNotification('error', `Erreur: ${error.message}`);
        }
    }
});

function applyFullWidthStyles() {
    document.querySelectorAll('.full-width').forEach(el => {
        el.style.width = '100%';
        el.style.padding = '1rem';
        el.style.marginBottom = '1.5rem';
        el.style.backgroundColor = 'var(--darker-bg)';
        el.style.border = '1px solid var(--border-color)';
        el.style.borderRadius = '0.5rem';
        el.style.color = 'var(--light-text)';
        el.style.fontFamily = 'inherit';
        el.style.fontSize = '1.4rem';
    });
}

function loadAnnouncements() {
    fetch('get-announcements.php')
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => { 
                    throw new Error(`Erreur ${res.status}: ${text}`) 
                });
            }
            return res.json();
        })
        .then(data => {
            const announcementsList = document.getElementById('announcements-list');
            
            if (!data.success) {
                throw new Error(data.message || 'Erreur inconnue du serveur');
            }

            announcementsList.innerHTML = data.announcements.length > 0
                ? data.announcements.map(ann => `
                    <div class="announcement">
                        <h3>${ann.title}</h3>
                        <div class="meta">
                            Par ${ann.author} le ${new Date(ann.created_at).toLocaleDateString('fr-FR')}
                        </div>
                        <p>${ann.content}</p>
                    </div>
                  `).join('')
                : '<p>Aucune annonce disponible.</p>';
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            const msg = error.message.includes('fetch') 
                ? 'Impossible de contacter le serveur'
                : error.message;
            
            document.getElementById('announcements-list').innerHTML = `
                <p class="error">Erreur: ${msg}</p>
            `;
        });
}

function showConfirmation(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'confirmation-modal';
    modal.innerHTML = `
        <div class="confirmation-content">
            <p>${message}</p>
            <div class="confirmation-buttons">
                <button class="confirm-btn">Confirmer</button>
                <button class="cancel-btn">Annuler</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const confirmBtn = modal.querySelector('.confirm-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    
    const cleanUp = () => {
        modal.remove();
    };
    
    confirmBtn.addEventListener('click', () => {
        cleanUp();
        callback(true);
    });
    
    cancelBtn.addEventListener('click', () => {
        cleanUp();
        callback(false);
    });
}