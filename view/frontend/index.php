<?php
$page_title = 'Le Bistrot - Accueil';
$page_header = false;

ob_start();
?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="cube-container">
        <div class="cube">
            <div class="front"></div>
            <div class="back"></div>
            <div class="right"></div>
            <div class="left"></div>
            <div class="top"></div>
            <div class="bottom"></div>
        </div>
    </div>
    <div class="hero-content">
        <div class="hero-subtitle" data-aos="fade-up">Expérience Culinaire Unique</div>
        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">Le Bistrot <span>Gastronomique</span></h1>
        <div class="hero-buttons" data-aos="fade-up" data-aos-delay="200">
            <a href="menu.php" class="btn btn-primary">
                <i class="fas fa-utensils"></i>
                Voir le Menu
            </a>
            <a href="reservation.php" class="btn btn-outline-light">
                <i class="fas fa-calendar-alt"></i>
                Réserver
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about" id="about">
    <div class="container">
        <div class="about-container">
            <div class="about-image" data-aos="fade-right">
                <img src="assets/img/about.jpg" alt="Restaurant Interior">
                <div class="about-experience">
                    <span class="number">15</span>
                    <span class="text">Années<br>d'Excellence</span>
                </div>
            </div>
            <div class="about-content" data-aos="fade-left">
                <p class="section-subtitle">À Propos</p>
                <h2 class="section-title">Découvrez Notre <span>Passion</span> Culinaire</h2>
                <p class="about-text">Notre restaurant représente l'excellence de la gastronomie, où la tradition rencontre l'innovation. Chaque plat est préparé avec passion et expertise, utilisant des ingrédients frais et de qualité.</p>
                <p class="about-text">Notre équipe de chefs talentueux crée des expériences culinaires uniques qui raviront vos papilles et éveilleront vos sens.</p>
                <div class="about-features">
                    <div class="feature" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                        <div class="feature-content">
                            <h4>Cuisine Traditionnelle</h4>
                            <p>Des recettes authentiques transmises de génération en génération.</p>
                        </div>
                    </div>
                    <div class="feature" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                        <div class="feature-content">
                            <h4>Ingrédients Frais</h4>
                            <p>Des produits locaux et de saison sélectionnés avec soin.</p>
                        </div>
                    </div>
                    <div class="feature" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-icon"><i class="fas fa-wine-glass-alt"></i></div>
                        <div class="feature-content">
                            <h4>Ambiance Élégante</h4>
                            <p>Un cadre raffiné pour une expérience gastronomique complète.</p>
                        </div>
                    </div>
                    <div class="feature" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <div class="feature-content">
                            <h4>Service Exceptionnel</h4>
                            <p>Un personnel attentionné pour vous offrir un service impeccable.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Preview Section -->
<section class="menu-preview" id="menu">
    <div class="container">
        <div class="menu-header" data-aos="fade-up">
            <p class="section-subtitle">Nos Spécialités</p>
            <h2 class="section-title">Découvrez Notre <span>Menu</span></h2>
        </div>
        <div class="menu-grid">
            <div class="menu-item" data-aos="fade-up" data-aos-delay="100">
                <div class="menu-item-image">
                    <img src="assets/img/plats.jpg" alt="Plats Principaux">
                </div>
                <div class="menu-item-content">
                    <h3 class="menu-item-title">Plats Principaux</h3>
                    <p class="menu-item-subtitle">Une sélection de nos meilleurs plats</p>
                    <div class="menu-item-price">À partir de 15 DT</div>
                    <a href="menu.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-right"></i>
                        Voir les plats
                    </a>
                </div>
            </div>
            <div class="menu-item" data-aos="fade-up" data-aos-delay="200">
                <div class="menu-item-image">
                    <img src="assets/img/entrees.jpg" alt="Entrées">
                </div>
                <div class="menu-item-content">
                    <h3 class="menu-item-title">Entrées</h3>
                    <p class="menu-item-subtitle">Des entrées fraîches et savoureuses</p>
                    <div class="menu-item-price">À partir de 8 DT</div>
                    <a href="menu.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-right"></i>
                        Voir les entrées
                    </a>
                </div>
            </div>
            <div class="menu-item" data-aos="fade-up" data-aos-delay="300">
                <div class="menu-item-image">
                    <img src="assets/img/desserts.jpg" alt="Desserts">
                </div>
                <div class="menu-item-content">
                    <h3 class="menu-item-title">Desserts</h3>
                    <p class="menu-item-subtitle">Des desserts traditionnels fait maison</p>
                    <div class="menu-item-price">À partir de 5 DT</div>
                    <a href="menu.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-right"></i>
                        Voir les desserts
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

$additional_css = '
<style>
    /* Hero Section */
    .hero {
        position: relative;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-color: var(--primary-dark);
    }

    .cube-container {
        position: absolute;
        width: 100%;
        height: 100%;
        perspective: 1000px;
    }

    .cube {
        position: absolute;
        width: 200px;
        height: 200px;
        transform-style: preserve-3d;
        animation: rotate 20s infinite linear;
    }

    .cube div {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid var(--accent-color);
    }

    .front { transform: translateZ(100px); }
    .back { transform: translateZ(-100px) rotateY(180deg); }
    .right { transform: rotateY(90deg) translateZ(100px); }
    .left { transform: rotateY(-90deg) translateZ(100px); }
    .top { transform: rotateX(90deg) translateZ(100px); }
    .bottom { transform: rotateX(-90deg) translateZ(100px); }

    @keyframes rotate {
        from { transform: rotateX(0) rotateY(0); }
        to { transform: rotateX(360deg) rotateY(360deg); }
    }

    .hero-content {
        text-align: center;
        z-index: 1;
        padding: 0 20px;
    }

    .hero-subtitle {
        font-size: 18px;
        color: var(--accent-color);
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-weight: 500;
    }

    .hero-title {
        font-size: 72px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 30px;
        font-family: "Playfair Display", serif;
        line-height: 1.2;
    }

    .hero-title span {
        color: var(--accent-color);
    }

    .hero-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    /* About Section */
    .about {
        padding: 100px 0;
        background-color: var(--secondary-dark);
    }

    .about-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 50px;
        align-items: center;
    }

    .about-image {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
    }

    .about-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .about-experience {
        position: absolute;
        bottom: 30px;
        right: 30px;
        background-color: var(--accent-color);
        color: var(--primary-dark);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }

    .about-experience .number {
        font-size: 48px;
        font-weight: 700;
        line-height: 1;
        font-family: "Playfair Display", serif;
    }

    .about-experience .text {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .about-content {
        padding-right: 50px;
    }

    .about-text {
        color: var(--text-secondary);
        margin-bottom: 30px;
        line-height: 1.8;
    }

    .about-features {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-top: 50px;
    }

    .feature {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-color);
        font-size: 20px;
        flex-shrink: 0;
    }

    .feature-content h4 {
        color: var(--text-primary);
        margin-bottom: 10px;
        font-size: 18px;
        font-family: "Playfair Display", serif;
    }

    .feature-content p {
        color: var(--text-secondary);
        font-size: 14px;
        line-height: 1.6;
    }

    /* Menu Preview Section */
    .menu-preview {
        padding: 100px 0;
        background-color: var(--primary-dark);
    }

    .menu-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .menu-item {
        background-color: var(--secondary-dark);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .menu-item:hover {
        transform: translateY(-10px);
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

    .menu-item-content {
        padding: 30px;
        text-align: center;
    }

    .menu-item-title {
        color: var(--text-primary);
        font-size: 24px;
        margin-bottom: 10px;
        font-family: "Playfair Display", serif;
    }

    .menu-item-subtitle {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 15px;
    }

    .menu-item-price {
        color: var(--accent-color);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .about-container {
            grid-template-columns: 1fr;
            gap: 50px;
        }

        .about-content {
            padding-right: 0;
        }

        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 48px;
        }

        .about-features {
            grid-template-columns: 1fr;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .hero-title {
            font-size: 36px;
        }

        .hero-subtitle {
            font-size: 14px;
        }

        .hero-buttons {
            flex-direction: column;
        }

        .about-experience {
            bottom: 20px;
            right: 20px;
            padding: 15px;
        }

        .about-experience .number {
            font-size: 36px;
        }

        .about-experience .text {
            font-size: 12px;
        }
    }
</style>';

require_once 'layout.php';
?> 