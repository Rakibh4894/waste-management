{{-- resources/views/website/home-page.blade.php --}}
@extends('website.master-without-sidebar')

@section('title', 'Dhaka Waste Management System')

@section('content')

{{-- External libraries --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.0.96/css/materialdesignicons.min.css" />

<!-- Leaflet JS (loaded later too; kept here for progressive rendering) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    :root{
        --bg: #f5f8fa;
        --card: rgba(255,255,255,0.85);
        --muted: #6b7280;
        --accent: #1eae63;
        --accent-2: #0f8a4f;
        --glass-border: rgba(255,255,255,0.45);
    }
    [data-theme="dark"]{
        --bg: #071226;
        --card: rgba(10,14,20,0.6);
        --muted: #9aa6bf;
        --accent: #28d08a;
        --accent-2: #1b8b55;
        --glass-border: rgba(255,255,255,0.06);
    }

    html,body{ height:100%; margin:0; font-family:'Poppins',sans-serif; background:var(--bg); color:#0f1724; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; transition: background .35s, color .35s; }

    /* NAV */
    .top-nav{
        display:flex; align-items:center; justify-content:space-between;
        gap:12px; padding:14px 20px; position:sticky; top:0; z-index:999;
        background: linear-gradient(180deg, rgba(255,255,255,0.7), rgba(255,255,255,0.55));
        backdrop-filter: blur(8px); border-bottom:1px solid rgba(0,0,0,0.04);
    }
    [data-theme="dark"] .top-nav{
        background: linear-gradient(180deg, rgba(12,16,24,0.55), rgba(12,16,24,0.45));
        border-bottom: 1px solid rgba(255,255,255,0.02);
    }
    .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; }
    .brand .logo {
        width:44px; height:44px; border-radius:10px; display:inline-grid; place-items:center;
        background: linear-gradient(180deg,var(--accent),var(--accent-2)); color:white; font-weight:700; font-size:18px;
        box-shadow: 0 8px 20px rgba(30,174,99,0.14);
    }
    .brand .title { font-weight:700; font-size:1.05rem; color:var(--muted); }
    .nav-links { display:flex; gap:12px; align-items:center; }
    .nav-link { color:var(--muted); text-decoration:none; padding:8px 12px; border-radius:8px; font-weight:600; }
    .nav-link:hover { background: rgba(0,0,0,0.03); color: inherit; }
    .auth-btns { display:flex; gap:10px; align-items:center; }

    .btn-primary {
        background: linear-gradient(180deg,var(--accent),var(--accent-2));
        color:white; padding:10px 16px; border-radius:12px; text-decoration:none; font-weight:700; display:inline-flex; gap:8px; align-items:center;
        box-shadow: 0 10px 28px rgba(30,174,99,0.16);
    }
    .btn-outline {
        background:transparent; border:1px solid rgba(0,0,0,0.06); color:var(--muted); padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:700;
    }

    /* HERO */
    .hero { position:relative; height:68vh; min-height:420px; overflow:hidden; border-bottom-left-radius:20px; border-bottom-right-radius:20px; }
    .hero-slide { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0; transition:opacity 1s ease; display:flex; align-items:center; justify-content:center; }
    .hero-slide.active{ opacity:1; z-index:2; }
    .hero-overlay { position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,0.42) 0%, rgba(0,0,0,0.56) 100%); z-index:1; }
    .hero-inner { position:relative; z-index:3; color:white; text-align:center; max-width:920px; padding:0 20px; }
    .hero-inner h1{ font-size:clamp(28px,4.2vw,48px); font-weight:800; line-height:1.05; margin-bottom:10px; letter-spacing:-0.6px; }
    .hero-inner p{ font-size:1.05rem; color:rgba(255,255,255,0.92); margin-bottom:16px; }

    .hero-actions { display:flex; gap:12px; justify-content:center; margin-top:12px; flex-wrap:wrap; }

    .hero-controls { position:absolute; left:20px; bottom:20px; z-index:4; display:flex; gap:8px; }
    .hero-dot { width:12px; height:12px; border-radius:50%; background: rgba(255,255,255,0.45); cursor:pointer; border:2px solid rgba(255,255,255,0.08); }
    .hero-dot.active{ background: var(--accent); box-shadow: 0 6px 16px rgba(0,0,0,0.22); }

    /* Floating actions */
    .fab-group { position:fixed; right:20px; bottom:28px; z-index:9999; display:flex; flex-direction:column; gap:12px; align-items:center; }
    .fab { display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:14px; background: linear-gradient(180deg,var(--accent),var(--accent-2)); color:white; text-decoration:none; box-shadow: 0 10px 30px rgba(30,174,99,0.18); transition: transform .18s ease; }
    .fab:hover{ transform: translateY(-6px); }
    .fab-label { display:block; background:var(--card); padding:8px 12px; border-radius:999px; box-shadow:0 8px 20px rgba(2,6,23,0.12); font-weight:600; font-size:14px; color:#06202a; margin-right:10px; }

    /* container & cards */
    .container { max-width:1140px; margin:0 auto; padding:36px 16px; }
    .glass-card { background:var(--card); border:1px solid var(--glass-border); border-radius:14px; padding:20px; box-shadow:0 8px 24px rgba(2,6,23,0.06); backdrop-filter: blur(6px); transition: transform .28s ease, box-shadow .28s ease; }
    .glass-card:hover{ transform: translateY(-6px); box-shadow:0 16px 40px rgba(2,6,23,0.12); }
    .section-title { font-size:1.6rem; margin-bottom:8px; font-weight:700; color:var(--muted); }
    .section-sub { color:var(--muted); margin-bottom:20px; }

    /* STATS */
    .stats-grid{ display:grid; gap:18px; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); }
    .stat-box { text-align:center; padding:18px; border-radius:12px; }
    .stat-number { font-weight:800; font-size:1.6rem; color:var(--accent); }
    .stat-label { color:var(--muted); margin-top:8px; }

    /* MAP */
    .map-container { width:100%; height:450px; border-radius:12px; overflow:hidden; border:1px solid var(--glass-border); box-shadow: 0 4px 18px rgba(0,0,0,0.12); }

    /* Responsive */
    @media (max-width:768px){
        .hero-controls{ left:12px; bottom:12px; }
        .fab-label{ display:none; }
        .nav-links{ display:none; }
    }

    /* Ensure contact section is at least 60px high */
    /* Contact card grid layout */
#contact .contact-card {
    min-height: 60px;
    text-align: left;
    border-radius: 14px;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Headings */
#contact h5 {
    color: var(--accent);
    margin-bottom: 6px;
}

/* Paragraph text */
#contact p {
    font-size: 0.95rem;
}

/* Social buttons */
#contact .btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    font-size: 18px;
    color: var(--muted);
    border: 1px solid var(--glass-border);
    transition: all 0.2s ease;
}

#contact .btn-outline:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent-2);
}

</style>

{{-- NAVIGATION --}}
<header class="top-nav" role="navigation" aria-label="Main navigation">
    <a href="{{ url('/') }}" class="brand" aria-label="Dhaka Waste Management Home">
        <div class="logo" aria-hidden="true">DW</div>
        <div>
            <div class="title">Dhaka Waste Management</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Clean City · Smart Service</div>
        </div>
    </a>

    <nav class="nav-links" aria-label="Primary">
        <a href="{{ url('/') }}" class="nav-link">Home</a>
        <a href="#whatWeDo" class="nav-link">What We Do</a>
        <a href="{{ url('/about') }}" class="nav-link">About</a>
        <a href="#contact" class="nav-link">Contact</a>
    </nav>

    <div class="auth-btns" aria-hidden="false">
        @guest
            <a href="{{ route('login') }}" class="btn-outline nav-link" aria-label="Login">Login</a>
            <a href="{{ route('register') }}" class="btn-primary" aria-label="Register">
                <i class="mdi mdi-account-plus" aria-hidden="true"></i> Register
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn-outline nav-link">Dashboard</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-outline nav-link" aria-label="Logout">Logout</button>
            </form>
        @endguest
    </div>
</header>

{{-- HERO SECTION --}}
<section class="hero" id="hero-slideshow" aria-label="Hero slideshow">
    <div class="hero-slide active" style="background-image:url('{{ asset('website/assets/images/waste-banner-1.jpg') }}');" data-index="0" role="img" aria-label="Banner 1">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1 style="color: white">Smart Waste Management for a Cleaner Dhaka</h1>
            <p>Real-time pickups, recycling support, and community reporting — all in one platform.</p>
            <div class="hero-actions">
                <a href="{{ route('waste-requests.create') }}" class="btn-primary" title="Request Pickup">
                    <i class="mdi mdi-truck-fast" aria-hidden="true"></i> Request Pickup
                </a>
                <a href="{{ route('register') }}" class="btn-outline" title="Create Account">Create Account</a>
            </div>
        </div>
    </div>

    <div class="hero-slide" style="background-image:url('{{ asset('website/assets/images/waste-banner-2.jpg') }}');" data-index="1" role="img" aria-label="Banner 2">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1 style="color: white">Report Issues — Fast Response</h1>
            <p>Flag uncollected waste, request urgent pickups, and view collection schedules.</p>
            <div class="hero-actions">
                <a href="{{ url('/contact') }}" class="btn-primary">Contact Support</a>
                <a href="{{ url('/complaint') }}" class="btn-outline">File Complaint</a>
            </div>
        </div>
    </div>

    <div class="hero-slide" style="background-image:url('{{ asset('website/assets/images/waste-banner-3.jpg') }}');" data-index="2" role="img" aria-label="Banner 3">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1 style="color: white">Together We Recycle</h1>
            <p>Find recycling centers, schedule pickups for bulk recyclables, and learn separation guidelines.</p>
            <div class="hero-actions">
                <a href="{{ url('/recycling-centers') }}" class="btn-primary">Find Centers</a>
                <a href="{{ route('waste-requests.create') }}" class="btn-outline">Request Bulk Pickup</a>
            </div>
        </div>
    </div>

    <div class="hero-controls" id="hero-dots" role="tablist" aria-label="Slideshow controls">
        <div class="hero-dot active" data-index="0" role="tab" aria-selected="true" aria-controls="slide-0" tabindex="0"></div>
        <div class="hero-dot" data-index="1" role="tab" aria-selected="false" aria-controls="slide-1" tabindex="0"></div>
        <div class="hero-dot" data-index="2" role="tab" aria-selected="false" aria-controls="slide-2" tabindex="0"></div>
    </div>
</section>


<div class="mode-toggle" style="position:fixed; left:20px; bottom:28px; z-index:9999;">
    <button id="darkModeToggle" class="mode-btn" title="Toggle Dark / Light Mode" aria-pressed="false" style="width:48px; height:48px; border-radius:10px; background:var(--card); display:flex; align-items:center; justify-content:center; border:1px solid var(--glass-border);">
        <i id="darkModeIcon" class="mdi mdi-weather-night" aria-hidden="true"></i>
    </button>
</div>


<section id= "whatWeDo" class="glass-card" style="margin-top:20px;" data-aos="fade-up">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:18px; flex-wrap:wrap;">
        <div>
            <div class="section-title">What We Do</div>
            <div class="section-sub">Faster, cleaner and smarter waste management for Dhaka city.</div>
            <p style="max-width:680px; color:var(--muted); margin-bottom:8px;">
                Our platform connects residents, collection crews, and administrators — enabling scheduled pickups, urgent requests and a city-wide recycling program.
            </p>
            <div style="display:flex; gap:10px; margin-top:12px;">
                <a href="{{ route('waste-requests.create') }}" class="btn-primary">Request Pickup</a>
                <a href="{{ route('register') }}" class="btn-outline">Create Account</a>
            </div>
        </div>

        <div style="flex-basis:380px;">
            <div class="stats-grid">
                <div class="stat-box glass-card" data-aos="zoom-in">
                    <div class="stat-number" data-target="15200">400</div>
                    <div class="stat-label">Pickups Completed</div>
                </div>
                <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-number" data-target="85">20</div>
                    <div class="stat-label">Active Trucks</div>
                </div>
                <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-number" data-target="120">200</div>
                    <div class="stat-label">Trained Workers</div>
                </div>
                <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-number" data-target="40">20</div>
                    <div class="stat-label">% Waste Recycled</div>
                </div>
            </div>
        </div>
    </div>
</section>
    {{-- WHAT WE DO --}}
    

    {{-- SERVICES --}}
    <section style="margin-top:26px;">
        <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:18px;">
            <div class="glass-card" data-aos="fade-up">
                <h3 style="margin-bottom:8px;">Real-Time Tracking</h3>
                <p style="color:var(--muted);">Track trucks, view live routes and monitor collection progress.</p>
            </div>

            <div class="glass-card" data-aos="fade-up" data-aos-delay="100">
                <h3 style="margin-bottom:8px;">Community Reporting</h3>
                <p style="color:var(--muted);">Report uncollected waste or bulky trash and get response estimates.</p>
            </div>

            <div class="glass-card" data-aos="fade-up" data-aos-delay="200">
                <h3 style="margin-bottom:8px;">Recycling Program</h3>
                <p style="color:var(--muted);">Schedule recyclable pickups and locate nearby recycling centers.</p>
            </div>
        </div>
    </section>
    

    {{-- LIVE MAP --}}
    <section class="container py-5" aria-label="Live map of pickups">
        <h2 class="section-title">Waste Pickup Activity</h2>
        <p class="section-sub">Live pickup requests and serviced areas across Dhaka</p>

        <div id="map" class="map-container" data-aos="fade-up" role="application" aria-label="Map showing pickups and trucks"></div>
    </section>
    
    {{-- CONTACT SECTION --}}
<section id="contact" class="container py-5" aria-label="Contact section">
    <h2 class="section-title text-center mb-4" data-aos="fade-up">Contact Us</h2>
    <p class="section-sub text-center mb-5" data-aos="fade-up" data-aos-delay="100">
        Reach out to Dhaka Waste Management for queries, suggestions, or urgent requests.
    </p>

    <div class="glass-card p-4 mx-auto contact-card" style="max-width:900px;" data-aos="fade-up" data-aos-delay="200">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:30px; align-items:center;">
            <div>
                <h5 class="fw-bold mb-2">Our Office</h5>
                <p style="color:var(--muted); margin-bottom:0;">
                    Dhaka Waste Management HQ<br>
                    Dhaka, Bangladesh<br>
                    Phone: +880 123 456 7890
                </p>
            </div>

            <div style="text-align:center;">
                <h5 class="fw-bold mb-2">Follow Us</h5>
                <div style="display:flex; gap:12px; justify-content:center;">
                    <a href="#" class="btn-outline p-2 rounded-circle"><i class="mdi mdi-facebook"></i></a>
                    <a href="#" class="btn-outline p-2 rounded-circle"><i class="mdi mdi-twitter"></i></a>
                    <a href="#" class="btn-outline p-2 rounded-circle"><i class="mdi mdi-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>




{{-- FOOTER --}}
<footer class="py-4 text-center" style="background:var(--card); border-top:1px solid var(--glass-border);">
    <div class="container">
        <p class="mb-0" style="color:var(--muted); font-size:14px;">
            &copy; {{ date('Y') }} Dhaka Waste Management. All rights reserved.
        </p>
        <p class="mb-0" style="color:var(--muted); font-size:13px; center;">
            Developed By <a href="#" target="_blank" style="color:var(--accent); text-decoration:none;">IJAH BD</a>
        </p>
    </div>
</footer>


@include('chat')



{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    // AOS
    AOS.init({ duration: 900, once: true });

    // ======= Hero Slideshow =======
    (function () {
        const slides = Array.from(document.querySelectorAll('.hero-slide'));
        const dots = Array.from(document.querySelectorAll('.hero-dot'));
        let current = 0;
        let timer = null;
        const INTERVAL = 4500;

        function show(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            current = index;
        }

        function next() { show((current + 1) % slides.length); }

        function start() { if (!timer) timer = setInterval(next, INTERVAL); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        start();

        dots.forEach(dot => dot.addEventListener('click', () => { stop(); show(parseInt(dot.dataset.index)); }));
        const hero = document.getElementById('hero-slideshow');
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);

        dots.forEach(el => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); }
            });
        });
    })();

    // ======= Count Up =======
    function animateCount(el, target) {
        const isPercent = String(target).includes('%');
        let final = Number(String(target).replace('%','')) || 0;
        const duration = 1400;
        const start = performance.now();
        function step(ts) {
            const t = Math.min(1, (ts - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3);
            const current = Math.round(eased * final);
            el.textContent = isPercent ? current + '%' : current.toLocaleString();
            if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    

    // ======= Leaflet Map =======
    (function initMap() {
        if (!window.L) return console.warn('Leaflet not loaded');
        const map = L.map('map', { scrollWheelZoom: false }).setView([23.7806, 90.2794], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        function addMarker(m) {
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="padding:6px 8px;border-radius:10px;background:#fff;border:1px solid #e6e6e6"><strong style="font-size:12px">${m.title}</strong></div>`,
                iconSize: [140, 36],
                iconAnchor: [70, 18]
            });
            L.marker([m.lat, m.lng], { icon }).addTo(map).bindPopup(`<strong>${m.title}</strong><br/>Status: ${m.status}`);
        }

        const sampleMarkers = [
            { lat: 23.7806, lng: 90.2794, title: 'Truck #12 (In Progress)', status: 'in_progress' },
            { lat: 23.7705, lng: 90.3500, title: 'Request #221 (Pending)', status: 'pending' },
            { lat: 23.7915, lng: 90.3760, title: 'Truck #4 (Idle)', status: 'idle' }
        ];

        // optional: fit to markers if any
        try {
            const group = new L.featureGroup();
            map.eachLayer(function(layer){
                if(layer instanceof L.Marker) group.addLayer(layer);
            });
            if(group.getLayers().length) map.fitBounds(group.getBounds().pad(0.4));
        } catch (e) { /* ignore */ }
    })();

    // ======= Dark mode toggle =======
    (function handleDarkMode() {
        const root = document.documentElement;
        const btn = document.getElementById('darkModeToggle');
        const icon = document.getElementById('darkModeIcon');
        const key = 'dwms_theme';

        function apply(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                icon.className = 'mdi mdi-weather-sunny';
                btn.setAttribute('aria-pressed', 'true');
            } else {
                document.documentElement.removeAttribute('data-theme');
                icon.className = 'mdi mdi-weather-night';
                btn.setAttribute('aria-pressed', 'false');
            }
        }

        const saved = localStorage.getItem(key);
        if (saved) apply(saved);
        else {
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            apply(prefersDark ? 'dark' : 'light');
        }

        btn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const next = isDark ? 'light' : 'dark';
            localStorage.setItem(key, next);
            apply(next);
        });
    })();

</script>


@endsection
