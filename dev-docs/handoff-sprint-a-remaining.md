# Agent Handoff: Sprint A Remaining + Sprint B

**Previous Agent**: Sprint A Hardening Agent
**Status**: Sprint A partially complete (10/13 items). All tests green: 165 passed, 0 failed.
**Audit Document**: `dev-docs/pre-flight-audit.md` (read this first for full context)

---

## What Was Done

The previous agent verified and fixed the following:

### P0 Issues (all 5 resolved)
- Issues 001-005 were already resolved in the codebase prior to the session.
- Three **pre-existing bugs** were discovered and fixed during verification:
  1. `lockForUpdate()->fresh()` is invalid (Builder has no `fresh()` method) -- fixed to `Model::query()->lockForUpdate()->findOrFail($id)` in `GamificationService.php` and `TrainingGamificationService.php`
  2. `GREATEST()` is PostgreSQL-only but tests use SQLite -- changed to `MAX()` in `User::updateStreak()`
  3. `UserFactory` didn't call `$user->refresh()` after creation, so DB-defaulted columns (`points`, `current_streak`, `longest_streak`) were missing under `Model::shouldBeStrict()` -- added `$user->refresh()` in `afterCreating`

### P1 Issues (4 resolved)
- **006**: `PartnerRequestModal` and `MilestoneCompleteModal` -- replaced silent `if/return` with `abort_unless()` in all action methods
- **011**: All 4 empty factory stubs filled (`MusterTaskFactory`, `TrainingMilestoneFactory`, `TrainingCheckinFactory`, `PartnerNotificationFactory`) with definitions and states
- **T2**: Created `TrainingGoalFactory` with states: `draft()`, `active()`, `completed()`, `verified()`, `withPartner()`, `pendingPartner()`
- **020**: Converted `$casts` property to `casts()` method on `TrainingMilestone`, `TrainingCheckin`, `PartnerNotification`

### Additional fixes
- Added `slug` to `TrainingGoal::$fillable` (safe: model auto-generates when empty, needed for tests)

---

## What You Need To Do

### Priority 1: Finish Sprint A (3 remaining items)

#### 1. Fix `User::rank()` N+1 (Issue 007)
**File**: `app/Models/User.php` (line ~274)
**Problem**: The `rank()` attribute runs `User::where('points', '>', $this->points)->count() + 1` on every access. In a leaderboard of 50 users, that's 50 COUNT queries.
**Fix options** (pick one):
- **Option A (recommended)**: Remove the `rank()` attribute entirely. In the gamification component (`resources/views/components/gamification/⚡gamification/gamification.php`), compute ranks in the `leaderboard()` query using `DB::raw('RANK() OVER (ORDER BY points DESC) as rank')`.
- **Option B**: Cache the rank per user with a short TTL (e.g., 60 seconds).
- Ensure any view referencing `$user->rank` is updated to use the new approach.

#### 2. Write Gamification Edge Case Tests (Issue 012)
**File**: Create `tests/Feature/GamificationServiceTest.php`
**Tests to write**:
- Streak resets to 1 when user misses a day (create muster 2 days ago, not yesterday, then check in today)
- Streak increments when user checks in on consecutive days
- Streak boundary badges are awarded at thresholds: 3, 7, 14, 21, 30, 60, 90 days
- Points milestone badges awarded at: 100, 250, 500, 1000, 2500, 5000, 10000 points
- Duplicate badge prevention (calling `earnBadge()` twice returns false the second time)
- `onMilestoneCompleted()` idempotency (calling twice only awards points once)
- Early bird bonus only awarded when `now()->hour < 9`
**Conventions**: Use Pest syntax (`it('...')` / `test('...')`), `RefreshDatabase`, factories. Seed badges with `BadgeSeeder` when testing badge awards. Follow patterns in `tests/Feature/Muster/MusterWizardTest.php`.

#### 3. Keyboard Accessibility for Drag-Drop (Issue 013)
**Files**: Task board and calendar blade views in `resources/views/livewire/`
**Problem**: Drag-and-drop uses Alpine mouse events only. Keyboard-only users can't reorder.
**Fix**: Add `tabindex="0"`, `role="option"`, `aria-grabbed`, and keyboard handlers (Space to grab, Arrow keys to move, Enter to drop) to draggable task cards and calendar events.

---

### Priority 2: Sprint B -- The "Game"

After finishing Sprint A, proceed to Sprint B. These are the P2 issues focused on gamification UX.

#### 1. Badge Unlock Hints (Issue 009)
- Create migration: `php artisan make:migration add_unlock_hint_to_badges_table --no-interaction`
- Add `$table->string('unlock_hint')->nullable()` column
- Update `database/seeders/BadgeSeeder.php` to include hints for all badges (e.g., "Check in 7 consecutive days" for `streak-7`)
- Display hint text on locked badges in `resources/views/components/gamification/⚡gamification/gamification.blade.php`

#### 2. Streak-at-Risk Warning (Issue 010)
- In the dashboard Livewire component, add a computed property: if `auth()->user()->todaysMuster()` is null and `now()->hour >= 18`, return true
- Display amber "Streak at risk -- check in before midnight!" banner on the dashboard view

#### 3. Scope Broadcast Channels (Issue 014)
- In `routes/channels.php`, the `muster` and `team` channels accept any authenticated user
- Gamification events should broadcast to user-specific private channels: `user.{id}`
- Update `PointsEarned`, `BadgeEarned` events to broadcast on `private-user.{userId}` instead of `muster`
- Keep `MusterCreated` on `team` channel (it's a team-visible event)
- Update frontend Echo listeners in `resources/js/echo.js` or equivalent

#### 4. Add Training Table Indexes (Issue 015)
- Create migration: `php artisan make:migration add_indexes_to_training_tables --no-interaction`
- Add indexes on:
  - `training_goals.user_id`
  - `training_goals.status`
  - `training_milestones.training_goal_id`
  - `training_checkins.training_goal_id`
  - `training_checkins.user_id`
  - `partner_notifications.user_id`
  - `partner_notifications.read_at`

#### 5. Implement Training Dashboard View (Issue 018)
- The route and component exist but the view is a stub
- Build out the blade template with: active goals list, partner goals, pending partner requests count, training stats summary
- Use Flux UI components consistent with the rest of the app
- Reference the existing `TrainingGoalShow` component patterns

#### 6. Badge Unlock Celebration (Issue 019)
- When `BadgeEarned` event is received via Echo, show an Alpine.js toast/modal with the badge icon, name, and point reward
- Keep it simple: a 3-second animated toast in the corner is sufficient

#### 7. Delete Dead Code (Issue 021)
- Delete `app/Models/StandUpFocusArea.php` (empty class, functionality handled by pivot)

#### 8. Soft-Delete Cascade (Issue 022)
- In `TrainingGoal::booted()`, add a `deleting` event that soft-deletes related milestones and check-ins
- Only cascade on soft-delete (check `$goal->isForceDeleting()`)

#### 9. Human-Readable Point Reasons (Issue 025)
- Create a helper method or use the existing `GamificationPoint` / `TrainingGamificationPoint` enums
- Map terse strings to labels: `"checkin"` -> `"Daily Muster Check-in"`, `"streak_bonus"` -> `"Streak Bonus"`, etc.
- Update the gamification page's point history section to use these labels

#### 10. Timezone Support (Issue 016)
- Add `timezone` column to users table (string, default `'UTC'`)
- Add to User `$fillable`
- Update `GamificationService::processCheckin()` early bird check: `now($user->timezone)->hour < 9`
- Update `User::todaysMuster()` and `updateStreak()` to use user timezone

---

## Important Conventions

- **Always run** `vendor/bin/pint --dirty --format agent` before finishing
- **Always run** `php artisan test --compact` after changes
- **Use** `php artisan make:*` commands to create new files (migrations, tests, etc.)
- **Tests** use Pest 4 syntax, `RefreshDatabase`, and model factories
- **Models** use `casts()` method, not `$casts` property
- **Read** `CLAUDE.md` for full project conventions
- **Update** `dev-docs/pre-flight-audit.md` after each fix -- change the issue Status from `OPEN` to `FIXED`

---

## Test Baseline

```
Tests: 1 skipped, 165 passed (443 assertions)
Duration: ~29s
```

Do not regress below this. All new tests must pass alongside existing ones.
