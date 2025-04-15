<?php require_once 'views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="card">
        <div class="card-header">
            <h3>Modifier le Profil</h3>
        </div>
        
        <div class="card-body">
            <form action="index.php?controller=user&action=update&id=<?php echo $user['id']; ?>" method="post" enctype="multipart/form-data">
                <div class="current-photo">
                    <div class="photo-preview">
                        <?php if(!empty($user['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Photo actuelle">
                            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($user['photo']); ?>">
                            <button type="button" class="remove-photo-btn" onclick="removePhoto(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php else: ?>
                            <div class="no-photo">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="remove_photo" id="remove_photo" value="0">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre" required>
                            <option value="Homme" <?php echo $user['genre'] == 'Homme' ? 'selected' : ''; ?>>Homme</option>
                            <option value="Femme" <?php echo $user['genre'] == 'Femme' ? 'selected' : ''; ?>>Femme</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" value="<?php echo $user['date_naissance']; ?>" required>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required>
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="mot_de_passe">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe">
                </div>
                
                <div class="form-group">
                    <label for="photo">Nouvelle photo de profil</label>
                    <div class="file-upload">
                        <input type="file" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
                        <label for="photo" class="upload-label">
                            <i class="fas fa-upload"></i> Choisir une nouvelle photo
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter" value="1" <?php echo $user['newsletter'] ? 'checked' : ''; ?>>
                        Je souhaite recevoir la newsletter
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="window.history.back()">Annuler</button>
                    <button type="submit" class="submit-btn">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.current-photo {
    margin-bottom: 2rem;
    text-align: center;
}

.photo-preview {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.photo-preview img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--gold-primary);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.upload-label i {
    font-size: 1.2rem;
}
</style>

<script>
function removePhoto(button) {
    const preview = button.parentElement;
    const defaultImage = 'assets/images/default-avatar.png';
    preview.querySelector('img').src = defaultImage;
    document.getElementById('remove_photo').value = '1';
    button.style.display = 'none';
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.photo-preview img');
            preview.src = e.target.result;
            document.getElementById('remove_photo').value = '0';
            const removeBtn = document.querySelector('.remove-photo-btn');
            if (removeBtn) {
                removeBtn.style.display = 'flex';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>
