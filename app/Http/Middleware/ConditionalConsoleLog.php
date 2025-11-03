<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConditionalConsoleLog
{
    /**
     * Handle an incoming request.
     *
     * This middleware intercepts console.log calls and only records them if debug mode is enabled
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Disabled - no longer capturing console logs
        if (false && auth('admin')->check()) {
            // Inject JavaScript to capture console.log calls
            if ($response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
                $content = $response->getContent();
                
                // Add script to capture console logs
                $script = <<<'JS'
<script>
(function() {
    const originalLog = console.log;
    const originalError = console.error;
    const originalWarn = console.warn;
    const originalInfo = console.info;
    
    function captureLog(level, ...args) {
        const timestamp = new Date().toISOString();
        const message = args.map(arg => {
            if (typeof arg === 'object') {
                return JSON.stringify(arg);
            }
            return String(arg);
        }).join(' ');
        
        // Send to server to log
        fetch('/admin/debug/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                level: level,
                message: message,
                timestamp: timestamp,
                url: window.location.href
            })
        }).catch(err => console.error('Failed to send debug log:', err));
        
        // Call original console method
        originalLog.apply(console, args);
    }
    
    console.log = function(...args) { captureLog('log', ...args); originalLog.apply(console, args); };
    console.error = function(...args) { captureLog('error', ...args); originalError.apply(console, args); };
    console.warn = function(...args) { captureLog('warn', ...args); originalWarn.apply(console, args); };
    console.info = function(...args) { captureLog('info', ...args); originalInfo.apply(console, args); };
})();
</script>
JS;
                
                // Inject script before closing </head> tag or at the beginning of <body>
                if (strpos($content, '</head>') !== false) {
                    $content = str_replace('</head>', $script . '</head>', $content);
                } else if (strpos($content, '<body') !== false) {
                    $content = str_replace('<body', $script . '<body', $content);
                }
                
                $response->setContent($content);
            }
        }
        
        return $response;
    }
}