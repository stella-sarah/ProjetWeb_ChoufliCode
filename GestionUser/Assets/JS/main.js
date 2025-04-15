document.addEventListener('DOMContentLoaded', function() {
    // Delete user confirmation
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    if (deleteButtons) {
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-id');
                
                if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                    window.location.href = `index.php?controller=user&action=delete&id=${userId}`;
                }
            });
        });
    }

    // Show password toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    if (passwordToggles) {
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordField = document.getElementById(this.getAttribute('data-for'));
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordField.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
    }

    // File input preview
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});