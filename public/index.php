<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

// FIX: Load Environment Variables Globally Immediately
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Helper: Render View
function view($path, $data = []) {
    extract($data);
    // Allow view to be found in subfolders (like admin/products)
    // or root views folder
    $viewPath = __DIR__ . "/../views/" . $path . ".php";
    
    if (file_exists($viewPath)) {
        // We include layout, and layout includes the specific view
        // passing $path to layout allows it to include the specific view file
        require __DIR__ . "/../views/layout.php";
    } else {
        echo "View not found: " . $path;
    }
}

// Helper: JSON Response
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route Collections
$routes = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

// Load Routes
require_once __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/api.php';

// 1. Check Static Routes first (Fastest)
if (isset($routes[$method][$uri])) {
    dispatch($routes[$method][$uri]);
    exit;
}

// 2. Check Dynamic Routes (Regex)
foreach ($routes[$method] as $routePattern => $action) {
    if (strpos($routePattern, '(') !== false) { 
        $pattern = "#^" . $routePattern . "$#";
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Remove full match
            dispatch($action, $matches);
            exit;
        }
    }
}

// 404 Handling
if (strpos($uri, '/api') === 0) {
    jsonResponse(['error' => 'Endpoint not found'], 404);
} else {
    http_response_code(404);
    echo "404 - Page Not Found";
}

// Dispatcher Function
function dispatch($action, $params = []) {
    if (is_callable($action)) {
        call_user_func_array($action, $params);
    } else {
        [$controllerName, $methodName] = explode('@', $action);
        $controllerClass = "Miziedi\\Controllers\\$controllerName";
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $methodName)) {
                call_user_func_array([$controller, $methodName], $params);
            } else {
                die("Method $methodName not found");
            }
        } else {
            die("Controller $controllerClass not found");
        }
    }
}