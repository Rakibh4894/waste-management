<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dhaka City Waste Management</title>
    @vite('resources/css/app.css') <!-- Tailwind via Vite -->
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- ✅ Navbar -->
    <header class="bg-green-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Dhaka Waste Management</h1>
            <nav class="space-x-6">
                <a href="#home" class="hover:text-gray-300">Home</a>
                <a href="#services" class="hover:text-gray-300">Services</a>
                <a href="#map" class="hover:text-gray-300">Zones</a>
                <a href="#contact" class="hover:text-gray-300">Contact</a>
                <a href="{{ route('login') }}" class="bg-white text-green-700 px-4 py-2 rounded-md font-semibold hover:bg-gray-200">Login</a>
            </nav>
        </div>
    </header>

    <!-- ✅ Hero Section -->
    <section id="home" class="bg-green-600 text-white text-center py-20">
        <h2 class="text-4xl md:text-5xl font-extrabold mb-4">Smart Waste Management for a Cleaner Dhaka</h2>
        <p class="text-lg mb-6">Request pickups, check collection schedules, and help keep your city clean.</p>
        <div class="space-x-4">
            <a href="{{ route('register') }}" class="bg-white text-green-700 px-6 py-3 rounded-lg font-bold shadow hover:bg-gray-200">Request Pickup</a>
            <a href="#services" class="bg-green-900 px-6 py-3 rounded-lg font-bold shadow hover:bg-green-800">Learn More</a>
        </div>
    </section>

    <!-- ✅ How It Works -->
    <section class="py-16 bg-white text-center">
        <h3 class="text-3xl font-bold mb-8">How It Works</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div class="p-6 bg-gray-50 rounded-lg shadow">
                <span class="text-green-700 text-4xl font-bold">1</span>
                <h4 class="text-xl font-semibold mt-4">Request Pickup</h4>
                <p>Citizens submit waste pickup requests online.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg shadow">
                <span class="text-green-700 text-4xl font-bold">2</span>
                <h4 class="text-xl font-semibold mt-4">Collector Assigned</h4>
                <p>Nearby waste collector receives notification.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg shadow">
                <span class="text-green-700 text-4xl font-bold">3</span>
                <h4 class="text-xl font-semibold mt-4">Waste Collected</h4>
                <p>Waste is picked up from your location on schedule.</p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg shadow">
                <span class="text-green-700 text-4xl font-bold">4</span>
                <h4 class="text-xl font-semibold mt-4">Cleaner Dhaka</h4>
                <p>Reports & feedback improve overall service quality.</p>
            </div>
        </div>
    </section>

    <!-- ✅ Services Section -->
    <section id="services" class="py-16 bg-gray-100 text-center">
        <h3 class="text-3xl font-bold mb-8">Our Services</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-white shadow p-6 rounded-xl">
                <h4 class="text-xl font-semibold mb-2">Waste Pickup Requests</h4>
                <p>Schedule a waste pickup from your home or office.</p>
            </div>
            <div class="bg-white shadow p-6 rounded-xl">
                <h4 class="text-xl font-semibold mb-2">Zone-based Collection</h4>
                <p>Check collection schedules by your ward or zone.</p>
            </div>
            <div class="bg-white shadow p-6 rounded-xl">
                <h4 class="text-xl font-semibold mb-2">Report Issues</h4>
                <p>Report overflowing bins or uncollected waste easily.</p>
            </div>
        </div>
    </section>

    <!-- ✅ Statistics -->
    <section class="py-16 bg-green-700 text-white text-center">
        <h3 class="text-3xl font-bold mb-8">Dhaka at a Glance</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div>
                <h4 class="text-4xl font-bold">120+</h4>
                <p>Requests Completed Today</p>
            </div>
            <div>
                <h4 class="text-4xl font-bold">95</h4>
                <p>Active Collectors</p>
            </div>
            <div>
                <h4 class="text-4xl font-bold">56</h4>
                <p>Zones Covered</p>
            </div>
            <div>
                <h4 class="text-4xl font-bold">500+</h4>
                <p>Citizens Served</p>
            </div>
        </div>
    </section>

    <!-- ✅ Footer -->
    <footer id="contact" class="bg-gray-900 text-gray-300 py-10">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h4 class="text-lg font-bold mb-4">Dhaka Waste Management</h4>
                <p>Smart Waste Management System for a Cleaner Dhaka City.</p>
            </div>
            <div>
                <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                <ul>
                    <li><a href="#home" class="hover:text-white">Home</a></li>
                    <li><a href="#services" class="hover:text-white">Services</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Register</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-bold mb-4">Contact</h4>
                <p>Email: support@dhakawaste.gov.bd</p>
                <p>Phone: +880 1234-567890</p>
            </div>
        </div>
        <div class="text-center mt-8 text-gray-500">
            © {{ date('Y') }} Dhaka City Corporation - Waste Management
        </div>
    </footer>

</body>
</html>
