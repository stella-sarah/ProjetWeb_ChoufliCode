<?php
$page_title = 'Restaurant - Menu';
$page_header = true;

require_once __DIR__ . '/../../controller/PlatC.php';

$platC = new PlatC();
$plats = $platC->afficherPlats();

ob_start();
?>

<!-- Menu Header -->
<section class="page-header">
    <div class="container">
        <p class="section-subtitle">Notre Menu</p>
        <h1 class="section-title">Découvrez Nos <span>Spécialités</span></h1>
    </div>
</section>

<!-- Menu Items -->
<section class="menu-section">
    <div class="container">
        <div class="menu-grid">
            <?php if ($plats): ?>
                <?php foreach ($plats as $plat): ?>
                <div class="menu-item" data-aos="fade-up">
                    <div class="menu-item-image">
                        <?php if (!empty($plat['image_url'])): ?>
                            <img src="../../<?php echo htmlspecialchars($plat['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($plat['name']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image">
                                <i class="fas fa-utensils"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="menu-item-content">
                        <div class="menu-item-header">
                            <h3 class="menu-item-title"><?php echo htmlspecialchars($plat['name']); ?></h3>
                            <div class="menu-item-price"><?php echo number_format($plat['price'], 2); ?> DT</div>
                        </div>
                        <?php if (!empty($plat['description'])): ?>
                            <p class="menu-item-description"><?php echo htmlspecialchars($plat['description']); ?></p>
                        <?php endif; ?>
                        <div class="menu-item-footer">
                            <?php if (!empty($plat['category'])): ?>
                                <div class="menu-item-category">
                                    <i class="fas fa-tag"></i>
                                    <span><?php echo htmlspecialchars($plat['category']); ?></span>
                                </div>
                            <?php endif; ?>
                            <a href="commande.php?plat_id=<?php echo $plat['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i>
                                Commander
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <i class="fas fa-utensils"></i>
                    <h3>Aucun plat disponible</h3>
                    <p>Notre menu est en cours de mise à jour.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    /* Menu Section */
    .menu-section {
        padding: 50px 0;
        background-color: var(--primary-dark);
        min-height: 400px;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    /* Menu Item */
    .menu-item {
        background-color: var(--secondary-dark);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.5s ease forwards;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .menu-item:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }

    .menu-item-image {
        position: relative;
        padding-top: 75%;
        overflow: hidden;
    }

    .menu-item-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .menu-item:hover .menu-item-image img {
        transform: scale(1.1);
    }

    .placeholder-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .placeholder-image i {
        font-size: 3rem;
        color: var(--accent-color);
    }

    .menu-item-content {
        padding: 20px;
    }

    .menu-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .menu-item-title {
        color: var(--text-primary);
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        font-family: "Playfair Display", serif;
    }

    .menu-item-price {
        color: var(--accent-color);
        font-size: 18px;
        font-weight: 600;
    }

    .menu-item-description {
        color: var(--text-secondary);
        margin-bottom: 20px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .menu-item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .menu-item-category {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .menu-item-category i {
        color: var(--accent-color);
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 30px;
        background-color: var(--accent-color);
        color: var(--primary-dark);
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: var(--accent-color-hover);
        transform: translateY(-2px);
    }

    .no-items {
        text-align: center;
        padding: 50px 20px;
        grid-column: 1 / -1;
    }

    .no-items i {
        font-size: 48px;
        color: var(--accent-color);
        margin-bottom: 20px;
    }

    .no-items h3 {
        color: var(--text-primary);
        margin-bottom: 10px;
        font-family: "Playfair Display", serif;
    }

    .no-items p {
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .menu-grid {
            grid-template-columns: 1fr;
            padding: 0 15px;
        }

        .menu-item-title {
            font-size: 18px;
        }

        .menu-item-price {
            font-size: 16px;
        }

        .menu-item-description {
            font-size: 14px;
        }
    }
</style>';

require_once 'layout.php';
?>