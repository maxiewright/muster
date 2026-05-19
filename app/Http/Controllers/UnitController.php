<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Models\Unit;
use App\Models\UnitMembership;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index(): Factory|View
    {
        abort_unless(auth()->user()?->canCreateUnits(), 403);

        return view('team.units.index', [
            'organization' => auth()->user()?->organization,
            'units' => Unit::query()
                ->where('organization_id', auth()->user()?->organization_id)
                ->withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $user->canCreateUnits(), 403);

        $unit = Unit::query()->create([
            'organization_id' => $user->organization_id,
            'name' => $request->string('name')->value(),
            'slug' => $this->nextAvailableSlug($user->organization_id, $request->string('name')->value()),
        ]);

        UnitMembership::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'unit_id' => $unit->id,
            ],
            [
                'role' => 'owner',
            ],
        );

        $request->session()->put('active_unit_id', $unit->id);

        return to_route('team.units.index')->with('status', 'Unit created successfully.');
    }

    private function nextAvailableSlug(int $organizationId, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'unit';
        $suffix = 2;

        while (Unit::query()
            ->where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->exists()) {
            $slug = sprintf('%s-%d', $baseSlug !== '' ? $baseSlug : 'unit', $suffix);
            $suffix++;
        }

        return $slug;
    }
}
