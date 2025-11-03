<?php
/**
 * Emergency Fix - Disable Problematic Middleware
 * This will edit the middleware files to disable the redirect loops
 */

// Security check
define('FIX_KEY', 'emergency123');

if (!isset($_GET['key']) || $_GET['key'] !== FIX_KEY) {
    http_response_code(403);
    die('Access Denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Emergency Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 600px; }
        .success { color: green; padding: 10px; background: #e8f5e9; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; border-radius: 4px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Emergency Middleware Fix</h1>
        
        <?php
        try {
            // Files to fix
            $files = [
                '../app/Http/Middleware/CheckEventEnded.php',
                '../app/Http/Middleware/AutomaticFeedbackChecker.php',
            ];
            
            foreach ($files as $filePath) {
                $fullPath = __DIR__ . '/' . $filePath;
                
                if (!file_exists($fullPath)) {
                    echo "<div class='error'>File not found: $filePath</div>";
                    continue;
                }
                
                // Read the file
                $content = file_get_contents($fullPath);
                
                // Check if already disabled
                if (strpos($content, 'TEMPORARILY DISABLED') !== false) {
                    echo "<div class='success'>✓ $filePath - Already disabled</div>";
                    continue;
                }
                
                // Find the process() method and replace it
                $oldPattern = '/protected function process\(Request \$request, Closure \$next\): Response\s*\{[\s\S]*?\n    \}/';
                
                $newContent = "    protected function process(Request \$request, Closure \$next): Response
    {
        // TEMPORARILY DISABLED TO FIX DATABASE CONNECTION LIMIT
        return \$next(\$request);
    }";
                
                // Try to replace
                $newContentFull = preg_replace($oldPattern, $newContent, $content);
                
                if ($newContentFull && $newContentFull !== $content) {
                    // Write the new content
                    file_put_contents($fullPath, $newContentFull);
                    echo "<div class='success'>✓ $filePath - Successfully disabled middleware</div>";
                } else {
                    echo "<div class='error'>✗ $filePath - Could not modify (pattern not found)</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <h2>Completed!</h2>
        <p>Please <strong>DELETE this file</strong> and clear your browser cache.</p>
        <p><strong>IMPORTANT:</strong> Clear your browser cache and cookies, then try accessing your site again.</p>
    </div>
</body>
</html>

