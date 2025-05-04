<?php
require_once __DIR__ . '/../../controller/PlatC.php';

$platC = new PlatC();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name']) && isset($_POST['price'])) {
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $image_url = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_url = $platC->uploadImage($_FILES['image']);
            if ($image_url === false) {
                $error = "Une erreur s'est produite lors du téléchargement de l'image.";
            }
        }
        
        if (!empty($name) && $price > 0 && !isset($error)) {
            if ($platC->ajouterPlat($name, $price, $image_url)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                exit;
            } else {
                $error = "Une erreur s'est produite lors de l'ajout du plat.";
            }
        } else if (!isset($error)) {
            $error = "Veuillez remplir tous les champs correctement.";
        }
    }
}

$plats = $platC->afficherPlats();

$page_title = 'Gestion des Plats';
ob_start();
?>

<!-- Success Messages -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Le plat a été ajouté avec succès!
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (isset($_GET['update_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Le plat a été modifié avec succès!
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (isset($_GET['delete_success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    Le plat a été supprimé avec succès!
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (isset($_GET['delete_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    Une erreur s'est produite lors de la suppression du plat.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Content Row -->
<div class="row">
    <!-- Add Dish Card -->  
     <link rel="stylesheet" href="css/styleback.css">
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Ajouter un nouveau plat</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="name">Nom du plat</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">
                                Veuillez entrer le nom du plat.
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="price">Prix (DT)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            <div class="invalid-feedback">
                                Veuillez entrer le prix du plat.
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="image">Image du plat</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choisir une image</label>
                            </div>
                            <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, WEBP</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter le plat</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dishes Grid -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Liste des plats</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($plats as $plat): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <?php if (!empty($plat['image_url'])): ?>
                    <img src="../../<?php echo htmlspecialchars($plat['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($plat['name']); ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-utensils fa-3x text-secondary"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title text-white"><?php echo htmlspecialchars($plat['name']); ?></h5>
                        <p class="card-text">
                            <strong class="text-primary"><?php echo number_format($plat['price'], 2); ?> DT</strong><br>
                            <small class="card-title text-white">Ajouté le <?php echo date('d/m/Y', strtotime($plat['created_at'])); ?></small>
                        </p>
                        <div class="btn-group">
                            <a href="modifier_plat.php?id=<?php echo $plat['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <button type="button" class="btn btn-primary" onclick="confirmDelete(<?php echo $plat['id']; ?>)">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Custom file input
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var label = e.target.nextElementSibling;
    label.innerHTML = fileName;
});

// Delete confirmation
function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce plat ?')) {
        window.location.href = 'supprimer_plat.php?id=' + id;
    }
}
</script>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?> 