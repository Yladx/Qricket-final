<?php
// Simple migration tool for qricket_feedback table
// Access via: https://yourdomain.com/migrate-qricket-feedback

// Basic security check
$allowedIPs = ['127.0.0.1', '::1']; // Add your server IP if needed
if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) && !isset($_GET['force'])) {
    die('Access denied. Add ?force to URL if you are sure.');
}

// Include Laravel bootstrap
require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "<h1>Qricket Feedback Table Migration</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Check if table exists
    if (Schema::hasTable('qricket_feedback')) {
        echo "<p style='color: green;'>✅ qricket_feedback table already exists!</p>";
        
        // Show table info
        $count = DB::table('qricket_feedback')->count();
        echo "<p>Records in table: $count</p>";
        
        // Show table structure
        $columns = DB::select("DESCRIBE qricket_feedback");
        echo "<h3>Table Structure:</h3><ul>";
        foreach ($columns as $column) {
            echo "<li>{$column->Field} - {$column->Type}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ qricket_feedback table does not exist. Creating...</p>";
        
        // Create the table
        Schema::create('qricket_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ticket_purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('feedback_type', ['website', 'event'])->default('website');
            
            // Website Feedback Ratings (1-5 stars)
            $table->tinyInteger('navigation_ease')->nullable();
            $table->tinyInteger('information_found')->nullable();
            $table->tinyInteger('design_rating')->nullable();
            $table->tinyInteger('performance_rating')->nullable();
            $table->tinyInteger('process_satisfaction')->nullable();
            
            // Open Feedback
            $table->text('improvements_suggestions')->nullable();
            $table->text('additional_comments')->nullable();

            // User/Session Info
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes for faster querying
            $table->index('user_id');
            $table->index('event_id');
            $table->index('feedback_type');
            $table->index('created_at');
        });
        
        echo "<p style='color: green;'>✅ qricket_feedback table created successfully!</p>";
        
        // Verify table was created
        if (Schema::hasTable('qricket_feedback')) {
            echo "<p style='color: green;'>✅ Table verification successful!</p>";
            
            // Show table structure
            $columns = DB::select("DESCRIBE qricket_feedback");
            echo "<h3>Created Table Structure:</h3><ul>";
            foreach ($columns as $column) {
                echo "<li>{$column->Field} - {$column->Type}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Table creation failed!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>✅ Admin dashboard should now work without errors</li>";
    echo "<li>✅ Feedback system is ready to use</li>";
    echo "<li>⚠️ Remove this migration file after successful creation</li>";
    echo "</ul>";
    
    echo "<p><a href='/admin/dashboard'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Migration completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
