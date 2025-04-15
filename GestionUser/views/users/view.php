<?php 
// Make sure $user is available before proceeding
if (!isset($user) || !is_array($user)) {
    die('User data not available');
}

// Include layout with absolute path
require_once 'views/layouts/admin.php';
?>

<div class="content-area">
    <div class="card">
        <div class="card-header">
            <h3>Détails de l'Utilisateur</h3>
            <div class="card-actions">
                <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="edit-btn">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <a href="index.php?controller=user&action=index" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="user-profile-view">
                <div class="user-avatar">
                    <?php if(!empty($user['photo'])): ?>
                        <img src="assets/uploads/<?php echo $user['photo']; ?>" alt="Photo de profil">
                    <?php else: ?>
                        <div class="default-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="user-details">
                    <div class="detail-group">
                        <span class="label">Nom:</span>
                        <span class="value"><?php echo $user['nom'] . ' ' . $user['prenom']; ?></span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo $user['email']; ?></span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Genre:</span>
                        <span class="value"><?php echo ucfirst($user['genre']); ?></span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Date de naissance:</span>
                        <span class="value"><?php echo date('d/m/Y', strtotime($user['date_naissance'])); ?></span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Téléphone:</span>
                        <span class="value"><?php echo $user['telephone']; ?></span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Rôle:</span>
                        <span class="value role-badge <?php echo $user['role']; ?>">
                            <?php echo $user['role'] == 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                        </span>
                    </div>

                    <div class="detail-group">
                        <span class="label">Newsletter:</span>
                        <span class="value"><?php echo $user['newsletter'] ? 'Oui' : 'Non'; ?></span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="label">Création du compte:</span>
                        <span class="value"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>