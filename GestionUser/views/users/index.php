<?php require_once 'views/layouts/header.php'; ?>

<!-- Le contenu sera inséré dans le content-wrapper -->
<div class="content-wrapper">
    <div class="card">
        <div class="card-header">
            <h3>Gestion des Utilisateurs</h3>
            <div class="card-actions">
                <div class="role-filter">
                    <select id="roleFilter" onchange="filterUsers()">
                        <option value="all">Tous les utilisateurs</option>
                        <option value="admin">Administrateurs</option>
                        <option value="user">Utilisateurs</option>
                    </select>
                </div>
                <a href="index.php?controller=user&action=create" class="add-btn">
                    <i class="fas fa-plus"></i> Ajouter un utilisateur
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="users-grid">
                <?php foreach ($users as $user): ?>
                    <div class="user-card" data-role="<?php echo $user['role']; ?>">
                        <div class="user-card-header">
                            <div class="user-avatar <?php echo empty($user['photo']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($user['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo de profil">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                                <span class="role-badge <?php echo $user['role']; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="user-card-body">
                            <div class="user-details">
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><i class="fas fa-calendar"></i> Inscrit le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            <div class="user-actions">
                                <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="action-btn edit" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="action-btn view" title="Voir" onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="index.php?controller=user&action=delete&id=<?php echo $user['id']; ?>" 
                                   class="action-btn delete" 
                                   title="Supprimer" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- User View Modal -->
<div class="user-view-modal" id="userViewModal">
    <div class="user-view-content">
        <div class="user-view-header">
            <div class="user-view-profile">
                <div class="user-avatar <?php echo empty($user['photo']) ? 'no-image' : ''; ?>" id="modalUserAvatarContainer">
                    <img id="modalUserPhoto" src="" alt="Photo de profil">
                </div>
                <div class="user-view-info">
                    <h3 id="modalUserName"></h3>
                    <span id="modalUserRole" class="role-badge"></span>
                </div>
            </div>
            <button class="user-view-close" onclick="closeUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="user-view-body">
            <div class="user-info-grid">
                <div class="info-group">
                    <h4><i class="fas fa-user"></i> Informations personnelles</h4>
                    <div class="info-item">
                        <span class="info-label">Nom complet</span>
                        <span id="modalFullName" class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span id="modalEmail" class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <span id="modalPhone" class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Genre</span>
                        <span id="modalGender" class="info-value"></span>
                    </div>
                </div>
                <div class="info-group">
                    <h4><i class="fas fa-clock"></i> Informations du compte</h4>
                    <div class="info-item">
                        <span class="info-label">Date d'inscription</span>
                        <span id="modalCreatedAt" class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dernière connexion</span>
                        <span id="modalLastLogin" class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Newsletter</span>
                        <span id="modalNewsletter" class="info-value"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterUsers() {
    const selectedRole = document.getElementById('roleFilter').value;
    const userCards = document.querySelectorAll('.user-card');
    
    userCards.forEach(card => {
        if (selectedRole === 'all' || card.dataset.role === selectedRole) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
// Add horizontal layout toggle
document.querySelector('.user-profile').addEventListener('click', function(e) {
    e.preventDefault();
    const profileCard = document.querySelector('.user-card[data-role="admin"]'); // Target admin card
    if(profileCard) profileCard.classList.toggle('horizontal');
});
function viewUser(user) {
    const modal = document.getElementById('userViewModal');
    const photo = document.getElementById('modalUserPhoto');
    const avatarContainer = document.getElementById('modalUserAvatarContainer');
    
    // Set user photo
    if (user.photo) {
        photo.src = user.photo;
        photo.style.display = 'block';
        avatarContainer.className = 'user-avatar';
    } else {
        photo.style.display = 'none';
        avatarContainer.className = 'user-avatar no-image';
        avatarContainer.innerHTML = '<i class="fas fa-user"></i>';
    }
    
    // Set user name and role
    document.getElementById('modalUserName').textContent = `${user.prenom} ${user.nom}`;
    document.getElementById('modalUserRole').textContent = user.role === 'admin' ? 'Administrateur' : 'Utilisateur';
    document.getElementById('modalUserRole').className = `role-badge ${user.role}`;
    
    // Set personal information
    document.getElementById('modalFullName').textContent = `${user.prenom} ${user.nom}`;
    document.getElementById('modalEmail').textContent = user.email;
    document.getElementById('modalPhone').textContent = user.telephone || 'Non renseigné';
    document.getElementById('modalGender').textContent = user.genre;
    
    // Set account information
    document.getElementById('modalCreatedAt').textContent = new Date(user.created_at).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('modalLastLogin').textContent = user.last_login || 'Jamais connecté';
    document.getElementById('modalNewsletter').textContent = user.newsletter ? 'Inscrit' : 'Non inscrit';
    
    // Show modal
    modal.style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userViewModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('userViewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUserModal();
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>