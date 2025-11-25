Route::get('/home-stats', function () {
    return response()->json([
        'pickups' => 15200,
        'trucks' => 85,
        'workers' => 120,
        'recycled' => 40,
    ]);
});
