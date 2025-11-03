<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track specific actions that should trigger feedback modal
        $this->trackAction($request);

        return $response;
    }

    /**
     * Track user actions for feedback modal triggers
     */
    private function trackAction(Request $request): void
    {
        $routeName = $request->route()?->getName();
        $method = $request->method();
        
        // Define actions that should trigger feedback modal
        $triggerActions = [
            'ticket_purchased' => [
                'routes' => ['tickets.purchase.success', 'additional-tickets.purchase.success'],
                'methods' => ['GET', 'POST']
            ],
            'subscription_purchased' => [
                'routes' => ['subscription.success'],
                'methods' => ['GET', 'POST']
            ],
            'event_registered' => [
                'routes' => ['events.register'],
                'methods' => ['POST']
            ],
            'ticket_shared' => [
                'routes' => ['tickets.share'],
                'methods' => ['POST']
            ]
        ];

        foreach ($triggerActions as $action => $config) {
            if (in_array($routeName, $config['routes']) && in_array($method, $config['methods'])) {
                $this->addUserAction($action);
                break;
            }
        }
    }

    /**
     * Add user action to session
     */
    private function addUserAction(string $action): void
    {
        $actions = session('qricket_user_actions', []);
        
        if (!in_array($action, $actions)) {
            $actions[] = $action;
            session(['qricket_user_actions' => $actions]);
        }
    }
}