<?php
// Get the router instance
$router = $GLOBALS['router'];

// Define API routes
$router->get('/api', function() {
    return json_encode([
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});

// Example routes for a REST API
$router->get('/api/users', function() {
    // This would typically call a controller or service
    return json_encode([
        'users' => [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith']
        ]
    ]);
});

$router->get('/api/users/:id', function($id) {
    // Example of a route with a parameter
    return json_encode([
        'user' => [
            'id' => $id,
            'name' => 'Sample User'
        ]
    ]);
});

$router->post('/api/users', function() {
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // This would typically validate and save the data
    return json_encode([
        'message' => 'User created successfully',
        'user' => $data
    ]);
});

$router->put('/api/users/:id', function($id) {
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // This would typically validate and update the data
    return json_encode([
        'message' => 'User updated successfully',
        'id' => $id,
        'user' => $data
    ]);
});

$router->delete('/api/users/:id', function($id) {
    // This would typically delete the user
    return json_encode([
        'message' => 'User deleted successfully',
        'id' => $id
    ]);
});
?>