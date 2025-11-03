<?php
/**
 * Web-based Migration and Seeding Tool
 * Run this file directly in your browser to migrate and seed your database
 * 
 * Usage: https://darkorange-spider-898858.hostingersite.com/migrate.php
 */

// Security check - only allow from specific IP or with password
$allowed_password = 'Qricket2024!'; // Change this password
$password_provided = $_GET['password'] ?? '';

if ($password_provided !== $allowed_password) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Migration Tool</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .form-group { margin: 20px 0; }
            input[type="password"] { width: 100%; padding: 10px; margin: 5px 0; }
            button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            button:hover { background: #005a87; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>🔧 Database Migration Tool</h1>
        <div class="warning">
            <strong>⚠️ Security Notice:</strong> This tool can modify your database. Only use this on staging/production with proper security.
        </div>
        <form method="GET">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Access Migration Tool</button>
        </form>
        <p><strong>Usage:</strong> Add ?password=YOUR_PASSWORD to the URL</p>
    </body>
    </html>
    ');
}

// Load Laravel
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Get action from URL
$action = $_GET['action'] ?? 'menu';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration Tool - Qricket</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; text-align: center; }
        .action-buttons { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin: 30px 0; 
        }
        .btn { 
            background: #007cba; 
            color: white; 
            padding: 15px; 
            text-decoration: none; 
            border-radius: 5px; 
            text-align: center; 
            display: block;
            transition: background 0.3s;
        }
        .btn:hover { background: #005a87; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .output { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 5px; 
            font-family: monospace; 
            white-space: pre-wrap; 
            max-height: 400px; 
            overflow-y: auto;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
        }
        .info { 
            background: #d1ecf1; 
            border: 1px solid #bee5eb; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
        }
        .back-btn { 
            background: #6c757d; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block; 
            margin: 20px 0; 
        }
        .back-btn:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Migration Tool</h1>
        
        <?php if ($action === 'menu'): ?>
            <div class="warning">
                <strong>⚠️ Important:</strong> This tool will modify your database. Make sure you have a backup before proceeding.
            </div>
            
            <div class="info">
                <strong>ℹ️ Available Actions:</strong>
                <ul>
                    <li><strong>Fresh Migration:</strong> Drop all tables and recreate them with seeders</li>
                    <li><strong>Run Migrations:</strong> Run pending migrations only</li>
                    <li><strong>Run Seeders:</strong> Run database seeders only</li>
                    <li><strong>Check Status:</strong> View current migration status</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="?password=<?= $allowed_password ?>&action=fresh" class="btn btn-danger">
                    🔄 Fresh Migration + Seed
                </a>
                <a href="?password=<?= $allowed_password ?>&action=migrate" class="btn">
                    ⬆️ Run Migrations
                </a>
                <a href="?password=<?= $allowed_password ?>&action=seed" class="btn btn-success">
                    🌱 Run Seeders
                </a>
                <a href="?password=<?= $allowed_password ?>&action=status" class="btn">
                    📊 Check Status
                </a>
            </div>
            
        <?php else: ?>
            <a href="?password=<?= $allowed_password ?>&action=menu" class="back-btn">← Back to Menu</a>
            
            <?php
            $output = '';
            $success = false;
            
            try {
                switch ($action) {
                    case 'fresh':
                        echo '<h2>🔄 Running Fresh Migration with Seeders</h2>';
                        echo '<div class="warning">This will DROP ALL TABLES and recreate them!</div>';
                        
                        $output .= "Starting fresh migration...\n";
                        $output .= "Dropping all tables...\n";
                        
                        // Run fresh migration with seeders
                        $exitCode = $kernel->call('migrate:fresh', ['--seed' => true]);
                        
                        if ($exitCode === 0) {
                            $output .= "✅ Fresh migration completed successfully!\n";
                            $output .= "✅ Database seeded with initial data!\n";
                            $success = true;
                        } else {
                            $output .= "❌ Fresh migration failed!\n";
                        }
                        break;
                        
                    case 'migrate':
                        echo '<h2>⬆️ Running Pending Migrations</h2>';
                        
                        $output .= "Checking for pending migrations...\n";
                        
                        // Run migrations
                        $exitCode = $kernel->call('migrate');
                        
                        if ($exitCode === 0) {
                            $output .= "✅ All migrations completed successfully!\n";
                            $success = true;
                        } else {
                            $output .= "❌ Migration failed!\n";
                        }
                        break;
                        
                    case 'seed':
                        echo '<h2>🌱 Running Database Seeders</h2>';
                        
                        $output .= "Running database seeders...\n";
                        
                        // Run seeders
                        $exitCode = $kernel->call('db:seed');
                        
                        if ($exitCode === 0) {
                            $output .= "✅ Database seeded successfully!\n";
                            $success = true;
                        } else {
                            $output .= "❌ Seeding failed!\n";
                        }
                        break;
                        
                    case 'status':
                        echo '<h2>📊 Migration Status</h2>';
                        
                        $output .= "Checking migration status...\n\n";
                        
                        // Get migration status
                        $exitCode = $kernel->call('migrate:status');
                        
                        if ($exitCode === 0) {
                            $output .= "✅ Migration status retrieved successfully!\n";
                            $success = true;
                        } else {
                            $output .= "❌ Failed to get migration status!\n";
                        }
                        break;
                        
                    default:
                        $output .= "❌ Invalid action specified!\n";
                }
                
            } catch (Exception $e) {
                $output .= "❌ Error: " . $e->getMessage() . "\n";
                $output .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
            }
            ?>
            
            <div class="output <?= $success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($output) ?>
            </div>
            
            <?php if ($success): ?>
                <div class="info">
                    <strong>✅ Operation completed successfully!</strong><br>
                    You can now use your application with the updated database.
                </div>
            <?php else: ?>
                <div class="warning">
                    <strong>⚠️ Operation failed!</strong><br>
                    Please check the output above for error details.
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <div class="info">
            <strong>🔗 Quick Links:</strong><br>
            <a href="https://darkorange-spider-898858.hostingersite.com" target="_blank">Visit Your Site</a> |
            <a href="https://darkorange-spider-898858.hostingersite.com/admin/login" target="_blank">Admin Login</a> |
            <a href="https://darkorange-spider-898858.hostingersite.com/webhook/xendit" target="_blank">Xendit Webhook</a>
        </div>
    </div>
</body>
</html>
