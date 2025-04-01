<?php
// Include the Database class
require_once './config/database.php';

// Create a function to test the database connection
function testDatabaseConnection() {
    // Create a new instance of the Database class
    $database = new Database();
    
    try {
        // Attempt to get a connection
        $conn = $database->getConnection();
        
        // Check if connection is successful
        if ($conn) {
            echo "<h2 style='color: green;'>Database Connection Test Successful ✓</h2>";
            echo "<p>Successfully connected to database: <strong>" . $database->db_name . "</strong></p>";
            
            // Display connection details
            echo "<h3>Connection Details:</h3>";
            echo "<ul>";
            echo "<li>Host: " . $database->host . "</li>";
            echo "<li>Database: " . $database->db_name . "</li>";
            echo "<li>Username: " . $database->username . "</li>";
            echo "</ul>";
            
            // Test a simple query to verify connection is fully working
            $query = "SELECT NOW() as server_time";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "<p>Query test: <strong>Successful</strong></p>";
                echo "<p>Server time: " . $result['server_time'] . "</p>";
            }
        } else {
            echo "<h2 style='color: red;'>Database Connection Test Failed ✗</h2>";
            echo "<p>Could not establish a connection to the database.</p>";
        }
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>Database Connection Test Failed ✗</h2>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

// Add some basic styling
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.6;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        ul {
            background-color: #f9f9f9;
            padding: 15px 15px 15px 40px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Database Connection Test</h1>";

// Run the test
testDatabaseConnection();

echo "</body>
</html>";
?>