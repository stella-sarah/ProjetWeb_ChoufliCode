<?php
require_once __DIR__ . '/../../controller/PlatC.php';

$platC = new PlatC();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: gestion_plats.php');
    exit;
}

$id = intval($_GET['id']);
$plat = $platC->afficherPlat($id);

// If dish not found, redirect back
if (!$plat) {
    header('Location: gestion_plats.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name']) && isset($_POST['price'])) {
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $image_url = $plat['image_url']; // Keep existing image by default
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $new_image_url = $platC->uploadImage($_FILES['image']);
            if ($new_image_url === false) {
                $error = "Une erreur s'est produite lors du téléchargement de l'image.";
            } else {
                // Delete old image if exists
                if ($image_url) {
                    $platC->deleteImage($image_url);
                }
                $image_url = $new_image_url;
            }
        }
        
        // Handle image deletion
        if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
            if ($image_url) {
                $platC->deleteImage($image_url);
                $image_url = null;
            }
        }
        
        if (!empty($name) && $price > 0 && !isset($error)) {
            if ($platC->modifierPlat($id, $name, $price, $image_url)) {
                header('Location: gestion_plats.php?update_success=1');
                exit;
            } else {
                $error = "Une erreur s'est produite lors de la modification du plat.";
            }
        } else if (!isset($error)) {
            $error = "Veuillez remplir tous les champs correctement.";
        }
    }
}

$page_title = 'Modifier le Plat';
ob_start();
?>

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
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Modifier le Plat</h6>
                <a href="gestion_plats.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="row">
                        <!-- Current Image Preview -->
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Image actuelle</h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (!empty($plat['image_url'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($plat['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($plat['name']); ?>" 
                                         class="img-fluid mb-3" style="max-height: 200px;">
                                    <div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="delete_image" name="delete_image" value="1">
                                            <label class="custom-control-label" for="delete_image">Supprimer l'image</label>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-image fa-4x text-secondary mb-3"></i>
                                        <p class="text-muted">Aucune image</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Fields -->
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name">Nom du plat</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($plat['name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le nom du plat.
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="price">Prix (DT)</label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo htmlspecialchars($plat['price']); ?>" 
                                           step="0.01" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le prix du plat.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Nouvelle image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Choisir une image</label>
                                </div>
                                <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, WEBP</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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

// Handle delete image checkbox
document.getElementById('delete_image')?.addEventListener('change', function(e) {
    var fileInput = document.getElementById('image');
    if (e.target.checked) {
        fileInput.disabled = true;
    } else {
        fileInput.disabled = false;
    }
});
</script>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?> 