<?php

namespace Paymenter\Extensions\Others\ForceDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceDashboardMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Redirect root to dashboard
        if ($request->is('/')) {
            return redirect('/dashboard');
        }

        // Return 404 only for home route
        if ($request->is('home')) {
            abort(404);
        }

        // Block main products page but allow cart, checkout, and product detail pages
        // Only block /products without any additional segments
        $path = $request->path();
        if ($path === 'products') {
            abort(404);
        }

        // Don't block /products/{category}, /products/{category}/{product}, /cart, /checkout, etc.
        return $next($request);
    }
}
