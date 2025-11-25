<!-- Optimized home-page.blade.php will be inserted here. -->
@extends('website.master')

@section('title', 'Dhaka Waste Management System')

@section('content')

{{-- External libraries --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-sA+e2k1V6w0g2gq2b8gkQGQ0pH6kYf5pFvQ6w5o8m+I=" crossorigin=""/>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<style>
    :root {
        --bg: #f5f8fa;
        --card: rgba(255,255,255,0.75);
        --muted: #6b7280;
        --accent: #1eae63;
        --glass-border: rgba(255,255,255,0.35);
    }

    [data-theme="dark"] {
        --bg: #0b1220;
        --card: rgba(10,14,20,0.6);
        --muted: #9aa6bf;
        --accent: #28d08a;
        --glass-border: rgba(255,255,255,0.06);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg);
        color: #0f1724;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        transition: background .35s, color .35s;
    }

    /* HERO SLIDESHOW */
    .hero {
        position: relative;
        height: 72vh;
        min-height: 420px;
        overflow: hidden;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    .hero-slide {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-slide.active { opacity: 1; z-index: 2; }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.55) 100%);
        z-index: 1;
    }

    .hero-inner {
        position: relative;
        z-index: 3;
        color: white;
        text-align: center;
        max-width: 920px;
        padding: 0 20px;
    }
    .hero-inner h1 { font-size: clamp(28px, 4.2vw, 48px); font-weight: 700; line-height: 1.05; margin-bottom: 12px;}
    .hero-inner p { font-size: 1.05rem; color: rgba(255,255,255,0.92); margin-bottom: 18px; }

    .hero-controls {
        position: absolute;
        left: 20px;
        bottom: 20px;
        z-index: 4;
        display:flex;
        gap:8px;
    }
    .hero-dot {
        width:12px; height:12px; border-radius:50%;
        background: rgba(255,255,255,0.45); cursor:pointer;
    }
    .hero-dot.active { background: var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,0.25); }

    /* FLOATING ACTIONS */
    .fab-group {
        position: fixed;
        right: 20px;
        bottom: 28px;
        z-index: 9999;
        display:flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }
    .fab {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:56px; height:56px; border-radius: 14px;
        background: linear-gradient(180deg, var(--accent), #0f8a4f);
        color: white; text-decoration: none;
        box-shadow: 0 10px 30px rgba(30,174,99,0.18);
        transition: transform .18s ease;
    }
    .fab:hover { transform: translateY(-6px); }

    .fab-label {
        display:block;
        background: var(--card);
        padding:8px 12px; border-radius: 999px;
        box-shadow: 0 8px 20px rgba(2,6,23,0.15);
        font-weight:600; font-size:14px; color: #06202a;
        margin-right: 10px;
    }

    /* GLASS CARDS & LAYOUT */
    .container {
        max-width: 1140px;
        margin: 0 auto;
        padding: 36px 16px;
    }
    .glass-card {
        background: var(--card);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 8px 24px rgba(2,6,23,0.06);
        backdrop-filter: blur(6px);
        transition: transform .28s ease, box-shadow .28s ease;
    }
    .glass-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(2,6,23,0.12); }

    .section-title {
        font-size: 1.6rem; margin-bottom: 8px; font-weight:700; color: var(--muted);
    }
    .section-sub { color: var(--muted); margin-bottom:20px;}

    /* STATS */
    .stats-grid { display:grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); }
    .stat-box { text-align:center; padding:18px; border-radius:12px; }

    .stat-number { font-weight:800; font-size:1.6rem; color:var(--accent); }
    .stat-label { color:var(--muted); margin-top:8px; }

    /* MAP */
    #map { width:100%; height:420px; border-radius:12px; overflow:hidden; border:1px solid var(--glass-border); }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .hero-controls { left: 12px; bottom: 12px; }
        .fab-label { display:none; }
    }

    /* Dark-mode small icon */
    .mode-toggle {
        position: fixed;
        left: 20px;
        bottom: 28px;
        z-index: 9999;
        display:flex;
        gap:8px;
    }
    .mode-btn {
        width:48px; height:48px; border-radius:10px; background:var(--card);
        display:flex; align-items:center; justify-content:center; border:1px solid var(--glass-border);
    }
</style>

{{-- HERO SECTION: slideshow of images --}}
<section class="hero" id="hero-slideshow" aria-label="Hero slideshow">
    {{-- slides (use your images in public/website/assets/images) --}}
    <div class="hero-slide active" style="background-image:url('{{ asset('website/assets/images/waste-banner-1.jpg') }}');" data-index="0" role="img" aria-label="Banner 1">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1>Smart Waste Management for a Cleaner Dhaka</h1>
            <p>Real-time pickups, recycling support, and community reporting — all in one platform.</p>

            <div style="display:flex; gap:12px; justify-content:center; margin-top:18px;">
                <a href="{{ route('waste-requests.create') }}" class="btn btn-lg btn-success shadow-lg px-4 py-2 rounded-pill">
                    <i class="mdi mdi-dump-truck me-2"></i> Request Pickup
                </a>

                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill">
                    Create Account
                </a>
            </div>
        </div>
    </div>

    <div class="hero-slide" style="background-image:url('{{ asset('website/assets/images/waste-banner-2.jpg') }}');" data-index="1" role="img" aria-label="Banner 2">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1>Report Issues — Fast Response</h1>
            <p>Flag uncollected waste, request urgent pickups, and view collection schedules.</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-top:18px;">
                <a href="{{ url('/contact') }}" class="btn btn-lg btn-light px-4 py-2 rounded-pill">Contact Support</a>
                <a href="{{ url('/complaint') }}" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill">File Complaint</a>
            </div>
        </div>
    </div>

    <div class="hero-slide" style="background-image:url('{{ asset('website/assets/images/waste-banner-3.jpg') }}');" data-index="2" role="img" aria-label="Banner 3">
        <div class="hero-overlay"></div>
        <div class="hero-inner" data-aos="fade-up">
            <h1>Together We Recycle</h1>
            <p>Find recycling centers, schedule pickups for bulk recyclables, and learn separation guidelines.</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-top:18px;">
                <a href="{{ url('/recycling-centers') }}" class="btn btn-lg btn-success px-4 py-2 rounded-pill">Find Centers</a>
                <a href="{{ route('waste-requests.create') }}" class="btn btn-outline-light btn-lg px-4 py-2 rounded-pill">Request Bulk Pickup</a>
            </div>
        </div>
    </div>

    {{-- dots --}}
    <div class="hero-controls" id="hero-dots" aria-hidden="false" role="tablist">
        <div class="hero-dot active" data-index="0" role="tab" aria-selected="true" aria-controls="slide-0" tabindex="0"></div>
        <div class="hero-dot" data-index="1" role="tab" aria-selected="false" aria-controls="slide-1" tabindex="0"></div>
        <div class="hero-dot" data-index="2" role="tab" aria-selected="false" aria-controls="slide-2" tabindex="0"></div>
    </div>
</section>

{{-- Floating action buttons + dark mode toggle --}}
<div class="fab-group" aria-hidden="false">
    <a href="{{ route('waste-requests.create') }}" class="fab" title="Request Pickup" aria-label="Request Pickup">
        <i class="mdi mdi-truck-fast fs-20"></i>
    </a>

    <div style="display:flex; align-items:center;">
        <span class="fab-label d-none d-md-inline">Quick Actions</span>
    </div>

    <a href="{{ url('/contact') }}" class="fab" title="Contact Support" aria-label="Contact Support" style="background: linear-gradient(180deg,#2b6ff7,#1a4fe0);">
        <i class="mdi mdi-phone fs-18"></i>
    </a>

    <a href="{{ url('/complaint') }}" class="fab" title="File Complaint" aria-label="File Complaint" style="background: linear-gradient(180deg,#ff6b6b,#ef4444);">
        <i class="mdi mdi-alert-circle-outline fs-18"></i>
    </a>
</div>

<div class="mode-toggle">
    <button id="darkModeToggle" class="mode-btn" title="Toggle Dark / Light Mode" aria-pressed="false">
        <i id="darkModeIcon" class="mdi mdi-weather-night"></i>
    </button>
</div>

{{-- MAIN CONTENT --}}
<main class="container" role="main">

    {{-- WHAT WE DO --}}
    <section class="glass-card" style="margin-top:-64px;" data-aos="fade-up">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:18px; flex-wrap:wrap;">
            <div>
                <div class="section-title">What We Do</div>
                <div class="section-sub">Faster, cleaner and smarter waste management for Dhaka city.</div>
                <p style="max-width:680px; color:var(--muted); margin-bottom:8px;">
                    Our platform connects residents, collection crews, and administrators — enabling scheduled pickups, urgent requests and a city-wide recycling program.
                </p>
                <div style="display:flex; gap:10px; margin-top:12px;">
                    <a href="{{ route('waste-requests.create') }}" class="btn btn-success px-4 py-2">Request Pickup</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark px-4 py-2">Create Account</a>
                </div>
            </div>

            <div style="flex-basis:380px;">
                <div class="stats-grid">
                    <div class="stat-box glass-card" data-aos="zoom-in">
                        <div class="stat-number" data-target="15200">0</div>
                        <div class="stat-label">Pickups Completed</div>
                    </div>
                    <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-number" data-target="85">0</div>
                        <div class="stat-label">Active Trucks</div>
                    </div>
                    <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-number" data-target="120">0</div>
                        <div class="stat-label">Trained Workers</div>
                    </div>
                    <div class="stat-box glass-card" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-number" data-target="40">0</div>
                        <div class="stat-label">% Waste Recycled</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SERVICES / CARDS --}}
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

#map {
    height: 450px !important;
}
<div id="map" class="map-container"></div>



    {{-- LIVE MAP --}}
    {{-- ============================= LIVE MAP SECTION ============================= --}}
<section class="container py-5">
    <h2 class="section-title">Waste Pickup Activity</h2>
    <p class="section-subtitle">Live pickup requests and serviced areas across Dhaka</p>

    <div class="map-container" id="map" data-aos="fade-up"></div>
</section>

<style>
    .map-container {
        height: 450px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(0,0,0,0.15);
    }
</style>



<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Create the map
        var map = L.map('map').setView([23.8103, 90.4125], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        // 1️⃣ Waste Pickup Requests (Markers)
        @if(isset($pickupRequests))
            @foreach($pickupRequests as $req)
                L.marker([{{ $req->latitude }}, {{ $req->longitude }}])
                    .addTo(map)
                    .bindPopup("<b>Pickup Request</b><br>Status: {{ $req->status }}");
            @endforeach
        @endif

        // 2️⃣ Serviced Areas (Polygons)
        @if(isset($servicedAreas))
            @foreach($servicedAreas as $area)
                L.polygon({!! $area->polygon_coordinates !!}, {
                    color: "green",
                    fillColor: "#34c759",
                    fillOpacity: 0.4
                })
                .addTo(map)
                .bindPopup("Serviced Area: {{ $area->name }}");
            @endforeach
        @endif

    });
</script>


    {{-- CTA --}}
    <section style="margin-top:26px; margin-bottom:60px;">
        <div class="cta glass-card" data-aos="zoom-in" style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 6px 0;">Join Dhaka’s Green Initiative</h3>
                <p style="margin:0; color:var(--muted);">Register and help reduce landfill waste with smarter pickups and recycling.</p>
            </div>
            <div style="display:flex; gap:12px;">
                <a href="{{ route('register') }}" class="btn btn-success px-4 py-2">Get Started</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-dark px-4 py-2">Contact</a>
            </div>
        </div>
    </section>
</main>

{{-- Libraries --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-o9N1j7kQGk0YwQv0KQpPnQ6bG9t4b2iGk1h+g3yZf4M=" crossorigin=""></script>

<script>
    // AOS init
    AOS.init({ duration: 900, once: true });

    // ======= Hero Slideshow =======
    (function () {
        const slides = Array.from(document.querySelectorAll('.hero-slide'));
        const dots = Array.from(document.querySelectorAll('.hero-dot'));
        let current = 0;
        let autoplay = true;
        let timer = null;
        const INTERVAL = 4500;

        function show(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            current = index;
        }

        function next() {
            show((current + 1) % slides.length);
        }

        // autoplay
        function start() { if (!timer) timer = setInterval(next, INTERVAL); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        start();

        // dot click
        dots.forEach(dot => dot.addEventListener('click', () => { stop(); show(parseInt(dot.dataset.index)); }));
        // pause on hover
        const hero = document.getElementById('hero-slideshow');
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);
    })();

    // ======= Count Up (simple) =======
    function animateCount(el, target) {
        const isPercent = String(target).includes('%');
        let value = 0;
        const final = Number(String(target).replace('%','')) || 0;
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

    (function loadStats() {
        const statEls = document.querySelectorAll('.stat-number');
        // default fallback values (keep them if no API)
        const fallback = {
            pickups: 15200,
            trucks: 85,
            workers: 120,
            recycledPercent: 40
        };

        // Try fetch from API endpoint if available
        fetch('/api/home-stats').then(r => {
            if (!r.ok) throw new Error('no-api');
            return r.json();
        }).then(data => {
            // expect { pickups, trucks, workers, recycledPercent }
            const pickups = data.pickups ?? fallback.pickups;
            const trucks = data.trucks ?? fallback.trucks;
            const workers = data.workers ?? fallback.workers;
            const recycledPercent = data.recycledPercent ?? fallback.recycledPercent;

            const targets = [pickups, trucks, workers, recycledPercent];
            statEls.forEach((el, i) => {
                animateCount(el, targets[i]);
            });
        }).catch(() => {
            // fallback
            const targets = [fallback.pickups, fallback.trucks, fallback.workers, fallback.recycledPercent];
            statEls.forEach((el, i) => animateCount(el, targets[i]));
        });
    })();

    // ======= Leaflet Map =======
    (function initMap() {
        const map = L.map('map', { scrollWheelZoom: false }).setView([23.7806, 90.2794], 12); // Dhaka center
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const sampleMarkers = [
            { lat: 23.7806, lng: 90.2794, title: 'Truck #12 (In Progress)', status: 'in_progress' },
            { lat: 23.7705, lng: 90.3500, title: 'Request #221 (Pending)', status: 'pending' },
            { lat: 23.7915, lng: 90.3760, title: 'Truck #4 (Idle)', status: 'idle' }
        ];

        function addMarker(m) {
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="padding:6px 8px;border-radius:10px;background:#fff;border:2px solid #e6e6e6"><strong style="font-size:12px">${m.title}</strong></div>`,
                iconSize: [140, 36],
                iconAnchor: [70, 18]
            });
            L.marker([m.lat, m.lng], { icon }).addTo(map).bindPopup(`<strong>${m.title}</strong><br/>Status: ${m.status}`);
        }

        // try to fetch dynamic markers from /api/map-data
        fetch('/api/map-data').then(r => {
            if (!r.ok) throw new Error('no-map-api');
            return r.json();
        }).then(data => {
            (data || sampleMarkers).forEach(addMarker);
        }).catch(() => {
            // fallback to sample markers if endpoint missing
            sampleMarkers.forEach(addMarker);
        });
    })();

    // ======= Dark mode toggle (persisted) =======
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

        // init
        const saved = localStorage.getItem(key);
        if (saved) apply(saved);
        else {
            // prefer dark if user's OS prefers dark
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

    // Accessibility: make hero dots keyboard accessible
    document.querySelectorAll('.hero-dot').forEach(el => {
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                el.click();
            }
        });
    });
</script>

@endsection
