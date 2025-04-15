document.addEventListener('DOMContentLoaded', function() {
    // Gestion du chatbot
    const chatbotToggle = document.querySelector('.chatbot-toggle');
    const chatbotContainer = document.querySelector('.chatbot-container');
    const closeChatbot = document.querySelector('.close-chatbot');
    
    chatbotToggle.addEventListener('click', function() {
        chatbotContainer.classList.toggle('active');
    });
    
    closeChatbot.addEventListener('click', function() {
        chatbotContainer.classList.remove('active');
    });
    
    // Simulation de réponse du chatbot
    const chatbotInput = document.querySelector('.chatbot-input input');
    const chatbotMessages = document.querySelector('.chatbot-messages');
    const chatbotSendBtn = document.querySelector('.chatbot-input button');
    
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            const userMessage = document.createElement('div');
            userMessage.classList.add('message');
            userMessage.textContent = message;
            chatbotMessages.appendChild(userMessage);
            
            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.classList.add('message', 'bot-message');
                botMessage.textContent = getBotResponse(message);
                chatbotMessages.appendChild(botMessage);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 1000);
            
            chatbotInput.value = '';
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
    }
    
    chatbotSendBtn.addEventListener('click', sendMessage);
    
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    function getBotResponse(message) {
        const lowerMsg = message.toLowerCase();
        
        if (lowerMsg.includes('bonjour') || lowerMsg.includes('salut')) {
            return "Bonjour! Comment puis-je vous aider à découvrir la Tunisie?";
        } else if (lowerMsg.includes('conseil') || lowerMsg.includes('recommande')) {
            return "Je vous recommande de visiter Sidi Bou Said pour ses magnifiques vues et son architecture bleue et blanche.";
        } else if (lowerMsg.includes('plage') || lowerMsg.includes('mer')) {
            return "Les meilleures plages se trouvent à Hammamet et Djerba. Le sable est fin et l'eau est cristalline!";
        } else if (lowerMsg.includes('désert') || lowerMsg.includes('sahara')) {
            return "Une excursion dans le désert du Sahara est inoubliable. Pensez à visiter Douz, la porte du désert.";
        } else if (lowerMsg.includes('merci')) {
            return "Avec plaisir! N'hésitez pas si vous avez d'autres questions.";
        } else {
            return "Je suis là pour vous aider à découvrir la Tunisie. Posez-moi vos questions sur les destinations, la culture ou la gastronomie!";
        }
    }
    
    // Gestion des emojis
    const emojiToggle = document.getElementById('emoji-toggle');
    const emojiList = document.getElementById('emoji-list');
    const newPostTextarea = document.getElementById('new-post-content');

    if (emojiToggle && emojiList) {
        emojiToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            emojiList.classList.toggle('hidden');
        });

        emojiList.querySelectorAll('span').forEach(emoji => {
            emoji.addEventListener('click', function() {
                newPostTextarea.value += this.textContent;
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

    // Gestion des médias
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

    // Chargement initial des posts
    loadPosts();

    // Gestion du formulaire de post
    const postForm = document.getElementById('post-form');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('post-title').value.trim();
            const content = document.getElementById('new-post-content').value.trim();
            
            if (!title) {
                alert("Veuillez ajouter un titre à votre publication.");
                return;
            }
            
            if (!content && !imageUpload.files[0] && !videoUpload.files[0]) {
                alert("Veuillez écrire quelque chose ou ajouter une image/vidéo.");
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
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur réseau: ' + error);
            });
        });
    }

    // Fonctionnalité de recherche
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
                console.error('Erreur de recherche:', error);
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

    // Gestion du modal de signalement
    document.querySelector('.close-modal').addEventListener('click', closeReportModal);

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('report-modal');
        if (event.target === modal) {
            closeReportModal();
        }
    });
});

// Fonction pour mettre à jour un post
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
            loadPosts(); // Recharger les posts
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour: ' + error.message);
    }
}

// Fonction pour mettre à jour un commentaire
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

        if (data.success) {
            const postElement = document.querySelector(`.comment[data-comment-id="${commentId}"]`).closest('.post');
            if (postElement) {
                const postId = postElement.dataset.postId;
                loadComments(postId, postElement);
            }
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour: ' + error.message);
    }
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
            console.error('Erreur de chargement des posts:', error);
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
            <p>${post.content}</p>
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
                <textarea placeholder="Ajouter un commentaire..."></textarea>
                <button class="btn-gold">Publier</button>
            </div>
            <div class="comment-list">
                ${post.comments ? post.comments.map(comment => `
                    <div class="comment" data-comment-id="${comment.id}">
                        <div class="default-avatar">${comment.author ? comment.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON'}</div>
                        <div class="comment-content">
                            <p class="author">${comment.author || 'Anonyme'} <span class="date">- ${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                            <p>${comment.content}</p>
                            <button class="delete-comment-btn" data-comment-id="${comment.id}" title="Supprimer ce commentaire">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="edit-comment-btn" data-comment-id="${comment.id}" title="Modifier ce commentaire">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                `).join('') : ''}
            </div>
        </div>
    `;

    const deleteBtn = article.querySelector('.delete-post-btn');
    deleteBtn.addEventListener('click', function() {
        if (confirm('Voulez-vous vraiment supprimer ce post ?')) {
            deletePost(post.id, article);
        }
    });

    const editPostBtn = article.querySelector('.edit-post-btn');
    if (editPostBtn) {
        editPostBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const postElement = this.closest('.post');
            const title = postElement.querySelector('h3').textContent;
            const content = postElement.querySelector('.post-content p').textContent;
            
            const editForm = document.createElement('div');
            editForm.innerHTML = `
                <div class="edit-form">
                    <input type="text" class="edit-title" value="${title.replace(/"/g, '&quot;')}">
                    <textarea class="edit-content">${content.replace(/"/g, '&quot;')}</textarea>
                    <button class="save-edit-btn">Enregistrer</button>
                    <button class="cancel-edit-btn">Annuler</button>
                </div>
            `;
            
            postElement.querySelector('.post-content').innerHTML = '';
            postElement.querySelector('.post-content').appendChild(editForm);
            
            editForm.querySelector('.cancel-edit-btn').addEventListener('click', function() {
                loadPosts();
            });
            
            editForm.querySelector('.save-edit-btn').addEventListener('click', function() {
                const newTitle = editForm.querySelector('.edit-title').value.trim();
                const newContent = editForm.querySelector('.edit-content').value.trim();
                
                if (newTitle && newContent) {
                    updatePost(postId, newTitle, newContent);
                } else {
                    alert('Le titre et le contenu ne peuvent pas être vides');
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
        const commentInput = this.previousElementSibling;
        const commentText = commentInput.value.trim();
        if (commentText) {
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
                    commentElement.innerHTML = `
                        <div class="default-avatar">${data.comment.author ? data.comment.author.split(' ').map(n => n[0]).join('').toUpperCase() : 'ANON'}</div>
                        <div class="comment-content">
                            <p class="author">${data.comment.author || 'Anonyme'} <span class="date">- ${new Date(data.comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                            <p>${data.comment.content}</p>
                            <button class="delete-comment-btn" data-comment-id="${data.comment.id}" title="Supprimer ce commentaire">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="edit-comment-btn" data-comment-id="${data.comment.id}" title="Modifier ce commentaire">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    `;
                    
                    const deleteBtn = commentElement.querySelector('.delete-comment-btn');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            if (!commentId) {
                                alert('ID de commentaire invalide');
                                return;
                            }
                            
                            if (confirm('Voulez-vous vraiment supprimer ce commentaire ?')) {
                                deleteComment(commentId, this.closest('.comment'));
                            }
                        });
                    }

                    const editBtn = commentElement.querySelector('.edit-comment-btn');
                    if (editBtn) {
                        editBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            const commentElement = this.closest('.comment');
                            const content = commentElement.querySelector('p').textContent;
                            
                            const editForm = document.createElement('div');
                            editForm.innerHTML = `
                                <div class="edit-form">
                                    <textarea class="edit-content">${content.replace(/"/g, '&quot;')}</textarea>
                                    <button class="save-edit-btn">Enregistrer</button>
                                    <button class="cancel-edit-btn">Annuler</button>
                                </div>
                            `;
                            
                            commentElement.querySelector('p').style.display = 'none';
                            commentElement.insertBefore(editForm, commentElement.querySelector('.edit-comment-btn'));
                            
                            editForm.querySelector('.cancel-edit-btn').addEventListener('click', function() {
                                editForm.remove();
                                commentElement.querySelector('p').style.display = 'block';
                            });
                            
                            editForm.querySelector('.save-edit-btn').addEventListener('click', function() {
                                const newContent = editForm.querySelector('.edit-content').value.trim();
                                
                                if (newContent) {
                                    updateComment(commentId, newContent);
                                } else {
                                    alert('Le contenu ne peut pas être vide');
                                }
                            });
                        });
                    }
                    
                    commentList.appendChild(commentElement);
                    commentInput.value = '';
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur réseau: ' + error);
            });
        }
    });

    article.querySelectorAll('.delete-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            if (!commentId) {
                alert('ID de commentaire invalide');
                return;
            }
            
            if (confirm('Voulez-vous vraiment supprimer ce commentaire ?')) {
                deleteComment(commentId, this.closest('.comment'));
            }
        });
    });

    article.querySelectorAll('.edit-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const commentElement = this.closest('.comment');
            const content = commentElement.querySelector('p').textContent;
            
            const editForm = document.createElement('div');
            editForm.innerHTML = `
                <div class="edit-form">
                    <textarea class="edit-content">${content.replace(/"/g, '&quot;')}</textarea>
                    <button class="save-edit-btn">Enregistrer</button>
                    <button class="cancel-edit-btn">Annuler</button>
                </div>
            `;
            
            commentElement.querySelector('p').style.display = 'none';
            commentElement.insertBefore(editForm, commentElement.querySelector('.edit-comment-btn'));
            
            editForm.querySelector('.cancel-edit-btn').addEventListener('click', function() {
                editForm.remove();
                commentElement.querySelector('p').style.display = 'block';
            });
            
            editForm.querySelector('.save-edit-btn').addEventListener('click', function() {
                const newContent = editForm.querySelector('.edit-content').value.trim();
                
                if (newContent) {
                    updateComment(commentId, newContent);
                } else {
                    alert('Le contenu ne peut pas être vide');
                }
            });
        });
    });

    const reportBtn = article.querySelector('.report-btn');
    reportBtn.addEventListener('click', function() {
        const postId = parseInt(this.closest('.post').dataset.postId);
        if (isNaN(postId) || postId <= 0) {
            alert('Erreur: ID de post invalide');
            return;
        }
        openReportModal(postId);
    });

    return article;
}

async function deletePost(postId, postElement) {
    try {
        const response = await fetch('delete-post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}`
        });

        const data = await response.json();

        if (data.success) {
            postElement.remove();
            alert('Post supprimé avec succès');
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression: ' + error.message);
    }
}

async function deleteComment(commentId, commentElement) {
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
            alert('Commentaire supprimé avec succès');
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert(error.message);
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
                    
                    commentElement.innerHTML = `
                        <div class="default-avatar">${initials}</div>
                        <div class="comment-content">
                            <p class="author">${comment.author || 'Anonyme'} <span class="date">- ${new Date(comment.created_at).toLocaleDateString('fr-FR')}</span></p>
                            <p>${comment.content}</p>
                            <button class="delete-comment-btn" data-comment-id="${comment.id}" title="Supprimer ce commentaire">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="edit-comment-btn" data-comment-id="${comment.id}" title="Modifier ce commentaire">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    `;
                    
                    const deleteBtn = commentElement.querySelector('.delete-comment-btn');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            if (confirm('Voulez-vous vraiment supprimer ce commentaire ?')) {
                                deleteComment(commentId, this.closest('.comment'));
                            }
                        });
                    }

                    const editBtn = commentElement.querySelector('.edit-comment-btn');
                    if (editBtn) {
                        editBtn.addEventListener('click', function() {
                            const commentId = this.getAttribute('data-comment-id');
                            const commentElement = this.closest('.comment');
                            const content = commentElement.querySelector('p').textContent;
                            
                            const editForm = document.createElement('div');
                            editForm.innerHTML = `
                                <div class="edit-form">
                                    <textarea class="edit-content">${content.replace(/"/g, '&quot;')}</textarea>
                                    <button class="save-edit-btn">Enregistrer</button>
                                    <button class="cancel-edit-btn">Annuler</button>
                                </div>
                            `;
                            
                            commentElement.querySelector('p').style.display = 'none';
                            commentElement.insertBefore(editForm, commentElement.querySelector('.edit-comment-btn'));
                            
                            editForm.querySelector('.cancel-edit-btn').addEventListener('click', function() {
                                editForm.remove();
                                commentElement.querySelector('p').style.display = 'block';
                            });
                            
                            editForm.querySelector('.save-edit-btn').addEventListener('click', function() {
                                const newContent = editForm.querySelector('.edit-content').value.trim();
                                
                                if (newContent) {
                                    updateComment(commentId, newContent);
                                } else {
                                    alert('Le contenu ne peut pas être vide');
                                }
                            });
                        });
                    }
                    
                    commentList.appendChild(commentElement);
                });
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function openReportModal(postId) {
    const modal = document.getElementById('report-modal');
    const postIdInput = document.getElementById('reported-post-id');
    
    postId = parseInt(postId);
    if (isNaN(postId) || postId <= 0) {
        alert('Erreur: ID de post invalide');
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
        
        alert('✔️ Signalement envoyé et post supprimé avec succès');
        closeReportModal();
        
    } catch (error) {
        console.error('Erreur de signalement:', error);
        
        if (error.name === 'AbortError') {
            alert('Erreur: Le serveur ne répond pas (timeout)');
        } else if (error.message.includes('Failed to fetch')) {
            alert('Erreur: Problème de connexion internet');
        } else {
            alert(`Erreur: ${error.message}`);
        }
    }
});