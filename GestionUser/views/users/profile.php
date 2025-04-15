<?php require_once 'views/layouts/header.php'; ?>

<div class="content-area">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-cover"></div>
            <div class="profile-avatar">
                <?php if(!empty($user['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo de profil">
                <?php else: ?>
                    <div class="no-photo">
                        <i class="fas fa-user-circle"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-info-header">
                <h1><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h1>
                <span class="role-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-actions">
                <a href="index.php?controller=user&action=edit&id=<?php echo $_SESSION['user_id']; ?>" class="edit-btn">
                    <i class="fas fa-edit"></i> Modifier le profil
                </a>
            </div>

            <div class="profile-details">
                <div class="detail-group">
                    <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Email</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Genre</label>
                            <span><?php echo htmlspecialchars($user['genre']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Date de naissance</label>
                            <span><?php echo date('d/m/Y', strtotime($user['date_naissance'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Téléphone</label>
                            <span><?php echo htmlspecialchars($user['telephone']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-group">
                    <h3><i class="fas fa-cog"></i> Préférences</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Newsletter</label>
                            <span><?php echo $user['newsletter'] ? 'Inscrit(e)' : 'Non inscrit(e)'; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Membre depuis</label>
                            <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 800px;
    margin: 0 auto;
    background: var(--dark-bg);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.profile-header {
    position: relative;
    text-align: center;
    padding-bottom: 20px;
}

.profile-cover {
    height: 200px;
    background: linear-gradient(45deg, var(--gold-dark), var(--gold-primary));
    position: relative;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    margin: -75px auto 20px;
    position: relative;
    z-index: 1;
}

.profile-avatar img,
.profile-avatar .no-photo {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 4px solid var(--dark-bg);
    background: var(--darker-bg);
    object-fit: cover;
}

.profile-avatar .no-photo {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4em;
    color: var(--gold-light);
}

.profile-info-header {
    margin-bottom: 20px;
}

.profile-info-header h1 {
    font-size: 24px;
    color: var(--gold-primary);
    margin-bottom: 10px;
}

.profile-content {
    padding: 20px;
}

.profile-actions {
    text-align: right;
    margin-bottom: 30px;
}

.profile-details {
    display: grid;
    gap: 30px;
}

.detail-group {
    background: rgba(201, 168, 108, 0.05);
    border-radius: 8px;
    padding: 20px;
}

.detail-group h3 {
    color: var(--gold-primary);
    font-size: 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item label {
    color: var(--gold-light);
    font-size: 0.9em;
    font-weight: 500;
}

.detail-item span {
    color: var(--light-text);
}

.edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background-color: var(--gold-primary);
    color: var(--darker-bg);
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.edit-btn:hover {
    background-color: var(--gold-light);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .profile-container {
        margin: 0;
        border-radius: 0;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'views/layouts/footer.php'; ?> 