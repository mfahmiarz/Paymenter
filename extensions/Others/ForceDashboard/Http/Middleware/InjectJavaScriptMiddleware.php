<?php

namespace Paymenter\Extensions\Others\ForceDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectJavaScriptMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Inject JavaScript into HTML response
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            
            // Check if this is an HTML response
            if (strpos($content, '<html') !== false) {
                $jsPath = __DIR__ . '/../../resources/js/force-dashboard.js';
                if (file_exists($jsPath)) {
                    $jsContent = file_get_contents($jsPath);
                    // Inject before </head> or </body>
                    if (strpos($content, '</head>') !== false) {
                        $content = str_replace('</head>', '<script>' . $jsContent . '</script></head>', $content);
                    } elseif (strpos($content, '</body>') !== false) {
                        $content = str_replace('</body>', '<script>' . $jsContent . '</script></body>', $content);
                    }
                    $response->setContent($content);
                }
            }
        }

        return $response;
    }
}
