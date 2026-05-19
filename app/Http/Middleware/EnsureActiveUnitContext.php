<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUnitContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $availableUnits = $user->units()
                ->orderBy('units.name')
                ->get();

            $activeUnitId = $request->session()->get('active_unit_id');
            $activeUnit = $availableUnits->firstWhere('id', $activeUnitId);

            if ($activeUnit === null) {
                $activeUnit = $availableUnits->first();

                if ($activeUnit !== null) {
                    $request->session()->put('active_unit_id', $activeUnit->id);
                } else {
                    $request->session()->forget('active_unit_id');
                }
            }

            $request->attributes->set('activeUnit', $activeUnit);
            View::share('activeUnit', $activeUnit);
            View::share('availableUnits', $availableUnits);
        }

        return $next($request);
    }
}
