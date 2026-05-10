<?php

namespace Paymenter\Extensions\Others\ForceDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectCSSMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Inject CSS into HTML response
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            
            // Check if this is an HTML response
            if (strpos($content, '<html') !== false) {
                $cssPath = __DIR__ . '/../../resources/css/hide-nav.css';
                if (file_exists($cssPath)) {
                    $cssContent = file_get_contents($cssPath);
                    // Inject before </head> or </body>
                    if (strpos($content, '</head>') !== false) {
                        $content = str_replace('</head>', '<style>' . $cssContent . '</style></head>', $content);
                    } elseif (strpos($content, '</body>') !== false) {
                        $content = str_replace('</body>', '<style>' . $cssContent . '</style></body>', $content);
                    }
                    $response->setContent($content);
                }
            }
        }

        return $response;
    }
}
