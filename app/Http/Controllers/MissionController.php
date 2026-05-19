<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMissionRequest;
use App\Models\Mission;
use App\Models\Unit;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class MissionController extends Controller
{
    public function index(): Factory|View|RedirectResponse
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        $activeUnit = $user->activeUnit();

        if (! $activeUnit instanceof Unit) {
            return $user->canCreateUnits()
                ? to_route('team.units.index')->with('status', 'Create or select a unit before managing missions.')
                : to_route('dashboard');
        }

        return view('missions.index', [
            'activeUnit' => $activeUnit,
            'canManageMissions' => $user->canManageMissions($activeUnit),
            'missions' => Mission::query()
                ->where('unit_id', $activeUnit->id)
                ->with(['commander'])
                ->withCount(['currentMemberships', 'actions'])
                ->latest('updated_at')
                ->get(),
            'unitMembers' => $activeUnit->users()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMissionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $activeUnit = $user?->activeUnit();

        abort_unless($user !== null && $activeUnit instanceof Unit && $user->canManageMissions($activeUnit), 403);

        $mission = Mission::query()->create([
            'organization_id' => $activeUnit->organization_id,
            'unit_id' => $activeUnit->id,
            'mission_commander_user_id' => $request->integer('mission_commander_user_id'),
            'name' => $request->string('name')->value(),
            'slug' => $this->nextAvailableSlug($activeUnit->id, $request->string('name')->value()),
            'description' => $request->string('description')->trim()->value() ?: null,
        ]);

        $rosterUserIds = collect($request->input('roster_user_ids', []))
            ->map(fn (mixed $userId): int => (int) $userId)
            ->filter()
            ->push($mission->mission_commander_user_id)
            ->unique()
            ->values();

        foreach ($rosterUserIds as $rosterUserId) {
            $mission->memberships()->create([
                'user_id' => $rosterUserId,
                'membership_type' => 'permanent',
                'added_by_user_id' => $user->id,
                'started_at' => now(),
            ]);
        }

        return to_route('missions.index')->with('status', 'Mission created successfully.');
    }

    private function nextAvailableSlug(int $unitId, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'mission';
        $suffix = 2;

        while (Mission::query()->where('unit_id', $unitId)->where('slug', $slug)->exists()) {
            $slug = sprintf('%s-%d', $baseSlug !== '' ? $baseSlug : 'mission', $suffix);
            $suffix++;
        }

        return $slug;
    }
}
