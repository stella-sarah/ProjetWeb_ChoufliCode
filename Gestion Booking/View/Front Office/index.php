<?php
// Front office main page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuniFy Village - Habitat de Luxe</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header / Navigation -->
    <header>
        <div class="container nav-container">
            <div class="logo">
                <img src="logo.png" alt="TuniFy Logo">
                <div class="logo-text">
                    <h1>TuniFy</h1>
                    <p>Village</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#about">À Propos</a></li>
                    <li><a href="#gallery">Galerie</a></li>
                    <li><a href="#features">Services</a></li>
                    <li><a href="reservation.php">Réservation</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <button class="contact-btn">Réserver une Visite</button>
        </div>
    </header>

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
            <div class="hero-subtitle">Expérience de Vie Luxueuse</div>
            <h1 class="hero-title">TuniFy <span>VILLAGE</span></h1>
            <p class="hero-description">Un havre résidentiel exquis où le luxe rencontre la tranquillité, niché au milieu de paysages à couper le souffle et d'équipements haut de gamme pour une expérience de vie inégalée.</p>
            <a href="#contact" class="hero-cta">Découvrir Plus</a>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container about-container">
            <div class="about-image">
                <img src="/api/placeholder/600/600" alt="TuniFy Village Luxury">
            </div>
            <div class="about-content">
                <p class="section-subtitle">À Propos de Notre Projet</p>
                <h2 class="section-title">Découvrez l'<span>Essence</span> de la Vie Luxueuse</h2>
                <p class="about-text">TuniFy Village représente le summum de l'habitat résidentiel de luxe, où l'architecture élégante rencontre le design moderne et le confort. Niché dans un emplacement privilégié, notre communauté exclusive offre aux résidents un mélange unique de tranquillité et de commodité.</p>
                <p class="about-text">Chaque détail a été méticuleusement élaboré pour garantir un style de vie d'une qualité sans compromis, des intérieurs aux accents dorés aux jardins paysagers luxuriants qui entourent nos propriétés.</p>
                <div class="about-features">
                    <div class="feature">
                        <div class="feature-icon">★</div>
                        <div class="feature-content">
                            <h4>Emplacement Premium</h4>
                            <p>Positionné stratégiquement avec un accès facile aux principales commodités et attractions.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">★</div>
                        <div class="feature-content">
                            <h4>Excellence Architecturale</h4>
                            <p>Des conceptions étonnantes qui mêlent élégance traditionnelle et innovation moderne.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">★</div>
                        <div class="feature-content">
                            <h4>Communauté Exclusive</h4>
                            <p>Un quartier sélect de résidents partageant les mêmes idées et profitant d'installations premium.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">★</div>
                        <div class="feature-content">
                            <h4>Développement Durable</h4>
                            <p>Pratiques écologiques intégrées dans la conception et la construction.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container gallery-container">
            <div class="gallery-header">
                <p class="section-subtitle">Voyage Visuel</p>
                <h2 class="section-title">Explorez Nos <span>Spectaculaires</span> Propriétés</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openTransportPopup('s3')">
                    <img src="/api/placeholder/600/350" alt="Villa S+3">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+3</h3>
                        <p class="gallery-item-subtitle">Résidence Executive</p>
                    </div>
                </div>
                <div class="gallery-item" onclick="openTransportPopup('s4')">
                    <img src="/api/placeholder/600/350" alt="Villa S+4">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Villa S+4</h3>
                        <p class="gallery-item-subtitle">Confort Moderne</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="/api/placeholder/600/350" alt="Piscine">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Piscines</h3>
                        <p class="gallery-item-subtitle">Loisirs & Détente</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="/api/placeholder/600/350" alt="Jardins Luxuriants">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Jardins Luxuriants</h3>
                        <p class="gallery-item-subtitle">Beauté Naturelle</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="/api/placeholder/600/350" alt="Centre de Fitness">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Centre de Fitness</h3>
                        <p class="gallery-item-subtitle">Santé & Bien-être</p>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="/api/placeholder/600/350" alt="Espaces Communautaires">
                    <div class="gallery-item-content">
                        <h3 class="gallery-item-title">Espaces Communautaires</h3>
                        <p class="gallery-item-subtitle">Rencontres Sociales</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Villa Popup -->
    <div class="transport-popup" id="transportPopup">
        <div class="transport-popup-content">
            <span class="close-popup" onclick="closeTransportPopup()">&times;</span>
            <h2 id="popupTitle">Détails de la Villa</h2>
            
            <div id="s3Popup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+3</span></li>
                        <li><span>Surface Totale</span><span>215m²</span></li>
                        <li><span>Sous-sol</span><span>52m²</span></li>
                        <li><span>Jardin</span><span>73m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>2</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 1</span></li>
                        <li><span>Chambres</span><span>3</span></li>
                        <li><span>Salles de Bain</span><span>2</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>650,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+3" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s3" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>

            <div id="s4Popup" class="popup-content">
                <div class="transport-details-section">
                    <h3>Caractéristiques</h3>
                    <ul class="transport-details-list">
                        <li><span>Type</span><span>S+4</span></li>
                        <li><span>Surface Totale</span><span>280m²</span></li>
                        <li><span>Sous-sol</span><span>65m²</span></li>
                        <li><span>Jardin</span><span>90m²</span></li>
                        <li><span>Piscine Extérieure</span><span>Oui</span></li>
                        <li><span>Places de Parking</span><span>3</span></li>
                    </ul>
                </div>
                
                <div class="transport-prices-section">
                    <h3>Détails Supplémentaires</h3>
                    <ul class="transport-prices-list">
                        <li><span>Étage</span><span>Rez-de-chaussée + 2</span></li>
                        <li><span>Chambres</span><span>4</span></li>
                        <li><span>Salles de Bain</span><span>3</span></li>
                        <li><span>Cuisine</span><span>Équipée</span></li>
                        <li><span>Prix</span><span>850,000 DT</span></li>
                    </ul>
                </div>

                <img src="/api/placeholder/600/300" alt="Plan Villa S+4" style="width:100%; margin-top:15px; border-radius:5px;">
                
                <a href="reservation-form.php?type=s4" class="book-now-btn">
                    <span>R</span><span>É</span><span>S</span><span>E</span><span>R</span><span>V</span><span>E</span><span>R</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container features-container">
            <div class="features-header">
                <p class="section-subtitle">Services Premium</p>
                <h2 class="section-title">Découvrez un <span>Luxe</span> et un Confort Inégalés</h2>
            </div>
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-box-icon">♨️</div>
                    <h3>Spa & Bien-être</h3>
                    <p>Profitez de nos installations de spa ultramodernes comprenant des salles de soins, saunas, hammams et espaces de relaxation pour une régénération ultime.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
                <div class="feature-box">
                    <div class="feature-box-icon">🏊</div>
                    <h3>Piscines à Débordement</h3>
                    <p>Plongez dans nos spectaculaires piscines à débordement avec vues panoramiques, solarium et cabanes privées.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
                <div class="feature-box">
                    <div class="feature-box-icon">🏋️</div>
                    <h3>Centre de Fitness</h3>
                    <p>Restez en forme dans notre centre de fitness de pointe équipé des derniers appareils de cardio et de musculation, avec des coachs personnels.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
                <div class="feature-box">
                    <div class="feature-box-icon">🍽️</div>
                    <h3>Gastronomie</h3>
                    <p>Savourez une cuisine exquise dans nos restaurants sur place offrant une gamme variée de plats internationaux et locaux préparés par des chefs renommés.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
                <div class="feature-box">
                    <div class="feature-box-icon">🏢</div>
                    <h3>Conciergerie 24/7</h3>
                    <p>Notre service de conciergerie dédié est disponible 24h/24 pour répondre à toutes vos demandes, des réservations de restaurant aux arrangements de voyage.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
                <div class="feature-box">
                    <div class="feature-box-icon">🔒</div>
                    <h3>Services de Sécurité</h3>
                    <p>Dormez tranquille grâce à notre système de sécurité complet comprenant une surveillance 24h/24, des contrôles d'accès sécurisés et du personnel de sécurité professionnel.</p>
                    <a href="#" class="feature-box-link">En Savoir Plus →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta">
        <div class="container cta-container">
            <h2 class="cta-title">Prêt à Vivre le Luxe?</h2>
            <p class="cta-text">Planifiez une visite privée de TuniFy Village et découvrez la maison de vos rêves dans notre communauté exclusive. Nos consultants chevronnés sont prêts à vous aider à trouver la résidence parfaite.</p>
            <div class="cta-buttons">
                <a href="#contact" class="cta-primary">Planifier une Visite</a>
                <a href="#" class="cta-secondary">Télécharger la Brochure</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container contact-container">
            <div class="contact-info">
                <p class="section-subtitle">Contactez-Nous</p>
                <h2 class="section-title">Nous <span>Joindre</span></h2>
                <p class="about-text">Vous avez des questions sur TuniFy Village? Notre équipe dévouée est là pour vous fournir toutes les informations dont vous avez besoin et vous accompagner dans votre parcours immobilier.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-item-content">
                            <h4>Notre Emplacement</h4>
                            <p>123 Boulevard du Luxe, Quartier Doré, Ville</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-item-content">
                            <h4>Numéro de Téléphone</h4>
                            <p><a href="tel:+21612345678">+216 12 345 678</a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-item-content">
                            <h4>Adresse Email</h4>
                            <p><a href="mailto:info@tunifyvillage.com">info@tunifyvillage.com</a></p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-social">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="contact-form">
                <form action="#" method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Votre Nom" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" placeholder="Votre Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" class="form-control" placeholder="Votre Téléphone">
                    </div>
                    <div class="form-group">
                        <select class="form-control" required>
                            <option value="" disabled selected>Intéressé Par</option>
                            <option value="Villa">Villa de Luxe</option>
                            <option value="Apartment">Appartement Premium</option>
                            <option value="Penthouse">Penthouse Exclusif</option>
                            <option value="General">Renseignement Général</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" placeholder="Votre Message" required></textarea>
                    </div>
                    <button type="submit" class="form-submit">Envoyer le Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-about">
                    <div class="logo">
                        <img src="logo.png" alt="TuniFy Logo">
                        <div class="logo-text">
                            <h1>TuniFy</h1>
                            <p>Village</p>
                        </div>
                    </div>
                    <p>TuniFy Village est l'incarnation du luxe, offrant une expérience résidentielle exclusive avec des équipements haut de gamme et un service exceptionnel dans un cadre magnifique.</p>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Liens Rapides</h3>
                    <ul>
                        <li><a href="#home">Accueil</a></li>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="#gallery">Propriétés</a></li>
                        <li><a href="#features">Services</a></li>
                        <li><a href="reservation.php">Réservation</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-heading">Nos Services</h3>
                    <ul>
                        <li><a href="#">Vente Immobilière</a></li>
                        <li><a href="#">Gestion de Propriété</a></li>
                        <li><a href="#">Design d'Intérieur</a></li>
                        <li><a href="#">Aménagement Paysager</a></li>
                        <li><a href="#">Services de Conciergerie</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3 class="footer-heading">Informations de Contact</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Boulevard du Luxe, Quartier Doré, Ville</p>
                    <p><i class="fas fa-phone"></i> +216 12 345 678</p>
                    <p><i class="fas fa-envelope"></i> info@tunifyvillage.com</p>
                    <p><i class="fas fa-clock"></i> Lun-Sam: 9:00 - 18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; 2025 TuniFy Village. Tous Droits Réservés. Conçu par <a href="#">Kaptin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Scroll Header Effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth Scrolling for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Villa Popup Functions
        function openTransportPopup(type) {
            const popup = document.getElementById('transportPopup');
            popup.style.display = 'flex';

            // Hide all popup contents first
            document.querySelectorAll('.popup-content').forEach(content => {
                content.style.display = 'none';
            });

            // Show the specific popup content
            if (type === 's3') {
                document.getElementById('s3Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+3 - Détails';
            } else if (type === 's4') {
                document.getElementById('s4Popup').style.display = 'block';
                document.getElementById('popupTitle').textContent = 'Villa S+4 - Détails';
            }
        }

        function closeTransportPopup() {
            const popup = document.getElementById('transportPopup');
            popup.classList.add('hiding');
            
            setTimeout(() => {
                popup.style.display = 'none';
                popup.classList.remove('hiding');
            }, 200);
        }

        // Close popup when clicking outside of it
        window.addEventListener('click', function(event) {
            const popup = document.getElementById('transportPopup');
            if (event.target === popup) {
                closeTransportPopup();
            }
        });
    </script>
</body>
</html>