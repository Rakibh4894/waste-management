<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dhaka Waste Management System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8fafc;
            scroll-behavior: smooth;
        }
        header {
            background: linear-gradient(to right, #008000, #00a65a);
            color: white;
        }
        .hero {
            padding: 120px 0;
            background: url('https://upload.wikimedia.org/wikipedia/commons/1/12/Dhaka_city_skyline.jpg') no-repeat center center/cover;
            color: white;
            text-align: center;
            position: relative;
        }
        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .feature-card {
            border: none;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        footer {
            background-color: #003300;
            color: #ddd;
            padding: 20px 0;
            text-align: center;
        }
        #map {
            height: 400px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!-- Header / Navbar -->
<header>
    <nav class="navbar navbar-expand-lg navbar-dark container">
        <a class="navbar-brand fw-bold" href="#">Dhaka Waste Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
                <li class="nav-item"><a href="#features" class="nav-link">Features</a></li>
                <li class="nav-item"><a href="#mapSection" class="nav-link">Map</a></li>
                <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
                <li class="nav-item"><a href="{{ route('login') }}" class="nav-link">Login</a></li>
                <li class="nav-item"><a href="{{ route('register') }}" class="btn btn-light text-success ms-2">Sign Up</a></li>
            </ul>
        </div>
    </nav>
</header>

<!-- Hero Section -->
<section class="hero d-flex align-items-center">
    <div class="container hero-content" data-aos="fade-up">
        <h1 class="display-4 fw-bold">Keep Dhaka Clean & Green</h1>
        <p class="lead">Smart Waste Management for a Sustainable City</p>
        <a href="{{ route('register') }}" class="btn btn-success btn-lg mt-3">Join the Initiative</a>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5" data-aos="fade-right">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">About the Project</h2>
        <p class="text-muted mx-auto" style="max-width: 700px;">
            The Dhaka Waste Management Web Application is a smart city solution that connects citizens, collectors, and city officials.
            Through digital waste tracking, pickup scheduling, and issue reporting, we aim to make Dhaka cleaner, greener, and more sustainable.
        </p>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container" data-aos="zoom-in-up">
        <h2 class="fw-bold text-center mb-5">Key Features</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <h5 class="fw-bold">Citizen Requests</h5>
                    <p class="text-muted">Submit waste pickup requests instantly from your account.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <h5 class="fw-bold">Zone Management</h5>
                    <p class="text-muted">Admins assign collectors to specific city zones for efficiency.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <h5 class="fw-bold">Issue Reporting</h5>
                    <p class="text-muted">Citizens can report waste problems with location and photos.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Dhaka Map Section -->
<section id="mapSection" class="py-5">
    <div class="container" data-aos="fade-up">
        <h2 class="fw-bold text-center mb-4">Dhaka Waste Collection Zones</h2>
        <p class="text-center text-muted mb-4">View active waste collection areas and ongoing operations.</p>
        <div id="map"></div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5" data-aos="fade-up">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Contact Us</h2>
        <p class="text-muted mb-3">Have questions or suggestions? We’d love to hear from you!</p>
        <a href="mailto:support@dhakawaste.gov.bd" class="btn btn-success">Email Us</a>
    </div>
</section>

<!-- Footer -->
<footer>
    <p class="mb-0">&copy; {{ date('Y') }} Dhaka City Waste Management System | All Rights Reserved</p>
</footer>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // Initialize AOS animations
    AOS.init({ duration: 1000, once: true });

    // Initialize Leaflet Map
    var map = L.map('map').setView([23.8103, 90.4125], 12); // Dhaka coordinates

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Example: markers for a few sample areas
    var zones = [
        { name: "Mirpur Zone", coords: [23.8223, 90.3654] },
        { name: "Gulshan Zone", coords: [23.7925, 90.4078] },
        { name: "Motijheel Zone", coords: [23.7310, 90.4210] }
    ];

    zones.forEach(zone => {
        L.marker(zone.coords).addTo(map)
            .bindPopup(`<b>${zone.name}</b><br>Active waste collection zone.`);
    });
</script>
</body>
</html>
