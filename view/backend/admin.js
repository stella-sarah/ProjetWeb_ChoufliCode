document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (!tabButtons.length || !tabContents.length) {
        console.warn('Aucun bouton ou contenu d\'onglet trouvé. Vérifiez les classes .tab-btn et .tab-content.');
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            const tabContent = document.getElementById(`${button.dataset.tab}-tab`);
            if (tabContent) {
                tabContent.classList.add('active');
            } else {
                console.error(`Contenu d'onglet #${button.dataset.tab}-tab non trouvé.`);
            }
        });
    });

    // Sélecteurs pour les modals et formulaires
    const addAnnouncementBtn = document.getElementById('add-announcement-btn');
    const announcementModal = document.getElementById('add-announcement-modal');
    const closeAnnouncementModal = document.getElementById('close-announcement-modal');
    const announcementForm = document.getElementById('announcement-form');
    const editModal = document.getElementById('edit-announcement-modal');
    const closeEditModal = document.getElementById('close-edit-modal');
    const editForm = document.getElementById('edit-announcement-form');

    // Vérifications des éléments
    if (!addAnnouncementBtn) console.error('Bouton #add-announcement-btn non trouvé.');
    if (!announcementModal) console.error('Modal #add-announcement-modal non trouvé.');
    if (!closeAnnouncementModal) console.error('Bouton #close-announcement-modal non trouvé.');
    if (!announcementForm) console.error('Formulaire #announcement-form non trouvé.');
    if (!editModal) console.error('Modal #edit-announcement-modal non trouvé.');
    if (!closeEditModal) console.error('Bouton #close-edit-modal non trouvé.');
    if (!editForm) console.error('Formulaire #edit-announcement-form non trouvé.');

    // Ouvrir le modal d'ajout d'annonce
    if (addAnnouncementBtn) {
        addAnnouncementBtn.addEventListener('click', () => {
            if (announcementModal) {
                announcementModal.style.display = 'block';
            }
        });
    }

    // Fermer le modal d'ajout
    if (closeAnnouncementModal) {
        closeAnnouncementModal.addEventListener('click', () => {
            if (announcementModal) {
                announcementModal.style.display = 'none';
                if (announcementForm) announcementForm.reset();
            }
        });
    }

    // Fermer le modal de modification
    if (closeEditModal) {
        closeEditModal.addEventListener('click', () => {
            if (editModal) editModal.style.display = 'none';
        });
    }

    // Fermer les modals en cliquant à l'extérieur
    window.addEventListener('click', (e) => {
        if (e.target === announcementModal) {
            announcementModal.style.display = 'none';
            if (announcementForm) announcementForm.reset();
        }
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // Fonction pour ouvrir le modal de modification
    function openEditModal(id) {
        if (!id) {
            console.error('ID d\'annonce manquant pour openEditModal.');
            showNotification('Erreur: ID d\'annonce manquant.', 'error');
            return;
        }

        fetch(`get-announcement.php?id=${id}`)
            .then(response => {
                if (!response.ok) throw new Error(`Erreur réseau: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success || !data.announcement) {
                    throw new Error(data.message || 'Données d\'annonce non valides.');
                }

                const { id, title, author, content } = data.announcement;
                const idField = document.getElementById('edit-announcement-id');
                const titleField = document.getElementById('edit-announcement-title');
                const authorField = document.getElementById('edit-announcement-author');
                const contentField = document.getElementById('edit-announcement-content');

                if (!idField || !titleField || !authorField || !contentField) {
                    console.error('Un ou plusieurs champs du formulaire de modification sont manquants.');
                    showNotification('Erreur: Formulaire de modification incomplet.', 'error');
                    return;
                }

                idField.value = id;
                titleField.value = title;
                authorField.value = author;
                contentField.value = content;

                if (editModal) {
                    editModal.style.display = 'block';
                } else {
                    console.error('Modal #edit-announcement-modal non trouvé pour affichage.');
                }
            })
            .catch(error => {
                console.error('Erreur dans openEditModal:', error);
                showNotification('Erreur lors du chargement de l\'annonce: ' + error.message, 'error');
            });
    }

    // Gestion de la soumission du formulaire de modification
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const idField = document.getElementById('edit-announcement-id');
            const titleField = document.getElementById('edit-announcement-title');
            const authorField = document.getElementById('edit-announcement-author');
            const contentField = document.getElementById('edit-announcement-content');

            if (!idField || !titleField || !authorField || !contentField) {
                console.error('Champs du formulaire de modification manquants.');
                showNotification('Erreur: Formulaire de modification incomplet.', 'error');
                return;
            }

            const id = idField.value.trim();
            const title = titleField.value.trim();
            const author = authorField.value.trim();
            const content = contentField.value.trim();

            // JavaScript validation
            if (!id) {
                showNotification('Erreur: ID d\'annonce manquant.', 'error');
                return;
            }
            if (!title) {
                showNotification('Veuillez ajouter un titre à l\'annonce.', 'error');
                return;
            }
            if (!author) {
                showNotification('Veuillez spécifier un auteur pour l\'annonce.', 'error');
                return;
            }
            if (!content) {
                showNotification('Veuillez ajouter du contenu à l\'annonce.', 'error');
                return;
            }

            const formData = new FormData(this);

            fetch('update-announcement.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`Erreur réseau: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Erreur inconnue');

                showNotification('Annonce mise à jour avec succès', 'success');
                if (editModal) editModal.style.display = 'none';
                loadAnnouncements();
                loadPublicAnnouncements();
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour:', error);
                showNotification('Erreur lors de la mise à jour: ' + error.message, 'error');
            });
        });
    }

    // Gestion de la soumission du formulaire d'ajout d'annonce
    if (announcementForm) {
        announcementForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const titleField = document.getElementById('announcement-title');
            const contentField = document.getElementById('announcement-content');
            const authorField = document.getElementById('announcement-author');

            if (!titleField || !contentField || !authorField) {
                console.error('Champs du formulaire d\'ajout manquants.');
                showNotification('Erreur: Formulaire d\'ajout incomplet.', 'error');
                return;
            }

            const title = titleField.value.trim();
            const content = contentField.value.trim();
            const author = authorField.value.trim();

            // JavaScript validation
            if (!title) {
                showNotification('Veuillez ajouter un titre à l\'annonce.', 'error');
                return;
            }
            if (!author) {
                showNotification('Veuillez spécifier un auteur pour l\'annonce.', 'error');
                return;
            }
            if (!content) {
                showNotification('Veuillez ajouter du contenu à l\'annonce.', 'error');
                return;
            }

            try {
                const response = await fetch('add-announcement.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}&author=${encodeURIComponent(author)}`
                });
                const data = await response.json();
                if (data.success) {
                    showNotification('Annonce ajoutée avec succès', 'success');
                    if (announcementModal) announcementModal.style.display = 'none';
                    announcementForm.reset();
                    loadAnnouncements();
                    loadPublicAnnouncements();
                } else {
                    showNotification(`Erreur: ${data.message || 'Échec de l\'ajout'}`, 'error');
                }
            } catch (error) {
                console.error('Erreur lors de l\'ajout:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        });
    }

    // Charger les données initiales
    loadPosts();
    loadComments();
    loadReports();
    loadAnnouncements();
    loadPublicAnnouncements();

    // Charger les publications
    function loadPosts() {
        const tbody = document.querySelector('#posts-table tbody');
        if (!tbody) {
            console.error('Tableau #posts-table tbody non trouvé.');
            return;
        }

        fetch('get-posts.php?admin=true')
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.success && Array.isArray(data.posts)) {
                    data.posts.forEach(post => {
                        const status = post.is_deleted ? 'Supprimé' : (post.hidden ? 'Caché' : 'Visible');
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${post.id}</td>
                            <td>${post.title}</td>
                            <td>${post.author || 'Anonyme'}</td>
                            <td>${new Date(post.created_at).toLocaleDateString('fr-FR')}</td>
                            <td>${status}</td>
                            <td>
                                ${post.is_deleted ? 
                                    `<button class="action-btn restore" data-id="${post.id}" data-type="post">Restaurer</button>` :
                                    `<button class="action-btn hide" data-id="${post.id}" data-type="post">${post.hidden ? 'Afficher' : 'Cacher'}</button>
                                     <button class="action-btn delete" data-id="${post.id}" data-type="post">Supprimer</button>`}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    attachActionListeners('post');
                } else {
                    console.warn('Aucune publication trouvée ou réponse invalide:', data);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des publications:', error));
    }

    // Charger les commentaires
    function loadComments() {
        const tbody = document.querySelector('#comments-table tbody');
        if (!tbody) {
            console.error('Tableau #comments-table tbody non trouvé.');
            return;
        }

        fetch('get-comments.php?admin=true')
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.success && Array.isArray(data.comments)) {
                    data.comments.forEach(comment => {
                        const status = comment.is_deleted ? 'Supprimé' : 'Visible';
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${comment.id}</td>
                            <td>${comment.post_id}</td>
                            <td>${comment.author || 'Anonyme'}</td>
                            <td>${comment.content.substring(0, 50)}${comment.content.length > 50 ? '...' : ''}</td>
                            <td>${new Date(comment.created_at).toLocaleDateString('fr-FR')}</td>
                            <td>${status}</td>
                            <td>
                                ${comment.is_deleted ? 
                                    `<button class="action-btn restore" data-id="${comment.id}" data-type="comment">Restaurer</button>` :
                                    `<button class="action-btn delete" data-id="${comment.id}" data-type="comment">Supprimer</button>`}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    attachActionListeners('comment');
                } else {
                    console.warn('Aucun commentaire trouvé ou réponse invalide:', data);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des commentaires:', error));
    }

    // Charger les signalements
    function loadReports() {
        const tbody = document.querySelector('#reports-table tbody');
        if (!tbody) {
            console.error('Tableau #reports-table tbody non trouvé.');
            return;
        }

        fetch('get-reports.php')
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.success && Array.isArray(data.reports)) {
                    data.reports.forEach(report => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${report.id}</td>
                            <td>${report.content_type === 'post' ? 'Publication' : 'Commentaire'}</td>
                            <td>${report.content_id}</td>
                            <td>${report.reporter}</td>
                            <td>${report.reason === 'spam' ? 'Spam' : 
                                  report.reason === 'harassment' ? 'Harcèlement' : 
                                  report.reason === 'hate_speech' ? 'Discours haineux' : 
                                  'Contenu inapproprié'}</td>
                            <td>${report.status === 'pending' ? 'En attente' : 
                                  report.status === 'resolved' ? 'Résolu' : 'Rejeté'}</td>
                            <td>
                                ${report.status === 'pending' ? `
                                    <button class="action-btn resolve" data-id="${report.id}" data-type="report">Résoudre</button>
                                    <button class="action-btn reject" data-id="${report.id}" data-type="report">Rejeter</button>
                                ` : ''}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    attachActionListeners('report');
                } else {
                    console.warn('Aucun signalement trouvé ou réponse invalide:', data);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des signalements:', error));
    }

    // Charger les annonces
    function loadAnnouncements() {
        const tbody = document.querySelector('#announcements-table tbody');
        if (!tbody) {
            console.error('Tableau #announcements-table tbody non trouvé.');
            return;
        }

        fetch('get-announcements.php')
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (data.success && Array.isArray(data.announcements)) {
                    data.announcements.forEach(announcement => {
                        const status = announcement.is_deleted ? 'Supprimé' : 'Visible';
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${announcement.id}</td>
                            <td>${announcement.title}</td>
                            <td>${announcement.author}</td>
                            <td>${announcement.content.substring(0, 100)}${announcement.content.length > 100 ? '...' : ''}</td>
                            <td>${new Date(announcement.created_at).toLocaleDateString('fr-FR')}</td>
                            <td>${status}</td>
                            <td>
                                ${announcement.is_deleted ? 
                                    `<button class="action-btn restore" data-id="${announcement.id}" data-type="announcement">Restaurer</button>` :
                                    `<button class="action-btn delete" data-id="${announcement.id}" data-type="announcement">Supprimer</button>`}
                            </td>
                            <td>
                                <button class="action-btn edit" data-id="${announcement.id}" data-type="announcement">Modifier</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    attachActionListeners('announcement');
                    attachEditListeners();
                } else {
                    console.warn('Aucune annonce trouvée ou réponse invalide:', data);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des annonces:', error));
    }

    // Charger les annonces publiques
    function loadPublicAnnouncements() {
        const announcementList = document.getElementById('announcements-list');
        if (!announcementList) {
            console.warn('Conteneur #announcements-list non trouvé pour les annonces publiques.');
            return;
        }

        fetch('get-announcements.php')
            .then(res => res.json())
            .then(data => {
                announcementList.innerHTML = '';
                if (data.success && Array.isArray(data.announcements)) {
                    data.announcements.forEach(ann => {
                        if (!ann.is_deleted) {
                            const div = document.createElement('div');
                            div.className = 'announcement';
                            div.innerHTML = `
                                <h3>${ann.title}</h3>
                                <div class="meta">Par <span>${ann.author}</span> | Publié le ${new Date(ann.created_at).toLocaleDateString('fr-FR')}</div>
                                <p>${ann.content}</p>
                            `;
                            announcementList.appendChild(div);
                        }
                    });
                } else {
                    console.warn('Aucune annonce publique trouvée ou réponse invalide:', data);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des annonces publiques:', error));
    }

    // Attacher les écouteurs pour les boutons d'édition
    function attachEditListeners() {
        const editButtons = document.querySelectorAll('.action-btn.edit');
        if (!editButtons.length) {
            console.warn('Aucun bouton .action-btn.edit trouvé pour attachEditListeners.');
        }

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (id) {
                    openEditModal(id);
                } else {
                    console.error('ID manquant pour le bouton d\'édition:', btn);
                }
            });
        });
    }

    // Attacher les écouteurs pour les actions (supprimer, restaurer, cacher, résoudre, rejeter)
    function attachActionListeners(type) {
        const buttons = document.querySelectorAll(`.action-btn[data-type="${type}"]`);
        if (!buttons.length) {
            console.warn(`Aucun bouton .action-btn trouvé pour le type ${type}.`);
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                const action = this.classList.contains('delete') ? 'delete' :
                              this.classList.contains('hide') ? 'hide' :
                              this.classList.contains('restore') ? 'restore' :
                              this.classList.contains('resolve') ? 'resolve' : 'reject';
                const typeFr = type === 'post' ? 'publication' :
                               type === 'comment' ? 'commentaire' :
                               type === 'report' ? 'signalement' : 'annonce';

                if (!id) {
                    console.error(`ID manquant pour l'action ${action} sur le type ${type}.`);
                    showNotification('Erreur: ID manquant.', 'error');
                    return;
                }

                if (action === 'delete') {
                    showConfirmation(`Êtes-vous sûr de vouloir supprimer cette ${typeFr} ?`, async (confirmed) => {
                        if (confirmed) {
                            try {
                                const response = await fetch(`admin-action.php`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: new URLSearchParams({
                                        type: type,
                                        action: action,
                                        id: id
                                    })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    showNotification(`${typeFr.charAt(0).toUpperCase() + typeFr.slice(1)} supprimée avec succès`, 'success');
                                    if (type === 'post') loadPosts();
                                    else if (type === 'comment') loadComments();
                                    else if (type === 'report') loadReports();
                                    else if (type === 'announcement') {
                                        loadAnnouncements();
                                        loadPublicAnnouncements();
                                    }
                                } else {
                                    showNotification(`Erreur: ${data.message || 'Échec de l\'action'}`, 'error');
                                }
                            } catch (error) {
                                console.error(`Erreur lors de l'action ${action} sur ${type}:`, error);
                                showNotification(`Erreur: ${error.message}`, 'error');
                            }
                        }
                    });
                    return;
                }

                try {
                    const response = await fetch(`admin-action.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            type: type,
                            action: action,
                            id: id
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showNotification(`${typeFr.charAt(0).toUpperCase() + typeFr.slice(1)} ${action === 'hide' ? 'cachée' : 
                            action === 'restore' ? 'restaurée' : 
                            action === 'resolve' ? 'résolue' : 'rejetée'} avec succès`, 'success');
                        if (type === 'post') loadPosts();
                        else if (type === 'comment') loadComments();
                        else if (type === 'report') loadReports();
                        else if (type === 'announcement') {
                            loadAnnouncements();
                            loadPublicAnnouncements();
                        }
                    } else {
                        showNotification(`Erreur: ${data.message || 'Échec de l\'action'}`, 'error');
                    }
                } catch (error) {
                    console.error(`Erreur lors de l'action ${action} sur ${type}:`, error);
                    showNotification(`Erreur: ${error.message}`, 'error');
                }
            });
        });
    }

    // Fonction pour afficher une notification stylisée
    function showNotification(message, type = 'info') {
        const types = {
            success: { icon: 'fa-check-circle', class: 'success' },
            error: { icon: 'fa-exclamation-circle', class: 'error' },
            warning: { icon: 'fa-exclamation-triangle', class: 'warning' },
            info: { icon: 'fa-info-circle', class: 'info' }
        };

        // Créer le conteneur s'il n'existe pas
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        // Créer la notification
        const notification = document.createElement('div');
        notification.className = `notification ${types[type].class}`;
        
        const icon = document.createElement('i');
        icon.className = `fas ${types[type].icon}`;
        
        const text = document.createElement('span');
        text.textContent = message;
        
        notification.appendChild(icon);
        notification.appendChild(text);
        container.appendChild(notification);

        // Supprimer la notification après l'animation
        setTimeout(() => {
            notification.remove();
            if (container && container.children.length === 0) {
                container.remove();
            }
        }, 3500);
    }

    // Fonction pour afficher une confirmation stylisée
    function showConfirmation(message, callback) {
        const modal = document.getElementById('confirmation-modal');
        const messageEl = document.getElementById('confirmation-message');
        const confirmBtn = document.getElementById('confirm-action');
        const cancelBtn = document.getElementById('cancel-action');

        if (!modal || !messageEl || !confirmBtn || !cancelBtn) {
            // Fallback si le modal n'est pas trouvé
            return callback(confirm(message));
        }

        messageEl.textContent = message;
        modal.style.display = 'block';

        const cleanUp = () => {
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            modal.style.display = 'none';
        };

        const onConfirm = () => {
            cleanUp();
            callback(true);
        };

        const onCancel = () => {
            cleanUp();
            callback(false);
        };

        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
    }
});