<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActiveUnitController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer'],
        ]);

        $unitId = (int) $validated['unit_id'];

        abort_unless(
            $request->user()?->units()->whereKey($unitId)->exists(),
            403,
        );

        $request->session()->put('active_unit_id', $unitId);

        return back();
    }
}
