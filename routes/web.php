<?php
// Get the router instance
$router = $GLOBALS['router'];

// Define web routes
$router->get('/', function() {
    return '<h1>Welcome to the PHP Backend</h1>';
});

$router->get('/about', function() {
    return '<h1>About Us</h1><p>This is a simple PHP backend.</p>';
});

$router->get('/contact', function() {
    return '<h1>Contact Us</h1><p>Email: info@example.com</p>';
});

// Example of a route with parameters
$router->get('/users/:id', function($id) {
    return '<h1>User Profile</h1><p>Viewing user with ID: ' . $id . '</p>';
});

// Example of a form submission
$router->get('/login', function() {
    return '
    <h1>Login</h1>
    <form method="POST" action="/login">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    ';
});

$router->post('/login', function() {
    // Process login
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    return "<h1>Login Successful</h1><p>Welcome, {$email}!</p>";
});
?>