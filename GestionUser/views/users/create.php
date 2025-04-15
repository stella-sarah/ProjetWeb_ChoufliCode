<?php require_once 'views/layouts/header.php'; ?>

<div class="content-area">
    <div class="card">
        <div class="card-header">
            <h3>Ajouter un Utilisateur</h3>
        </div>
        
        <div class="card-body">
            <?php if(isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="index.php?controller=user&action=store" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre" required>
                            <option value="">Sélectionner le genre</option>
                            <option value="Homme">Homme</option>
                            <option value="Femme">Femme</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" required>
                            <option value="">Sélectionner le rôle</option>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="photo">Photo de profil</label>
                    <div class="file-upload">
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <label for="photo">Choisir une photo</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter" value="1">
                        Je souhaite recevoir la newsletter
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="accepte_conditions" value="1" required>
                        J'accepte les conditions d'utilisation
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="window.history.back()">Annuler</button>
                    <button type="submit" class="submit-btn">Créer l'utilisateur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>