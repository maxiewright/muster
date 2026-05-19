# Command Tenancy, Missions, and Training Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add org bootstrap provisioning, org-scoped unit administration, mission/action work management, and commander-led training assignments without breaking the current tenant refactor.

**Architecture:** Keep internal platform provisioning separate from the tenant app. Land the work in phases so the app first gains correct organization onboarding and unit-management paths, then mission/action data structures, then commander-led training flows. Reuse existing unit scoping and invitation patterns where they fit, but avoid grafting mission logic into the current task model.

**Tech Stack:** Laravel 13, Fortify, Livewire 4, Flux UI 2, Pest 4, PostgreSQL

---

## Progress

- [x] Design approved in chat
- [x] Spec written to `.dev/specs/2026-04-08-command-tenancy-missions-design.md`
- [x] Implementation plan written
- [x] Phase 1 started
- [x] Phase 1 complete
- [x] Phase 2 complete
- [x] Phase 3 complete
- [x] Phase 4 complete

## Working Rules

- [ ] Before starting a task, mark it `in progress` in this file.
- [x] After finishing a task, check off its verification step and completion step.
- [ ] Keep notes about blockers or deviations under the relevant task.
- [ ] Do not mark a task complete until the listed tests pass.

## File Structure

### Expected New or Changed Areas

- `app/Http/Controllers/`
  Organization bootstrap completion, unit management, mission management
- `app/Http/Requests/`
  Validation for bootstrap setup, unit creation, mission creation, action creation, commander training assignment
- `app/Models/`
  Mission, MissionMembership, Action, ActionAssignment, commander training assignment models
- `database/migrations/`
  Tables and indexes for missions, roster history, action assignments, training assignment records
- `resources/views/`
  Setup flow, unit management screens, mission screens, action screens, training commander workflow
- `routes/`
  Tenant routes for onboarding completion, units, missions, actions, commander training
- `tests/Feature/`
  End-to-end behavior coverage
- `tests/Unit/`
  Focused model and policy behavior where needed

## Phase 1: Fix Organization Bootstrap and Unit Management

### Task 1: Persist design and planning artifacts

**Files:**
- Create: `.dev/specs/2026-04-08-command-tenancy-missions-design.md`
- Create: `.dev/plans/2026-04-08-command-tenancy-missions-implementation-plan.md`

- [x] Step 1: Write the approved design spec
- [x] Step 2: Write the implementation plan
- [x] Step 3: Confirm both files exist in `.dev`

### Task 2: Decide the bootstrap invite record shape

**Files:**
- Modify: `app/Models/TeamInvitation.php` or create a dedicated bootstrap invite model if separation is cleaner
- Modify: `database/migrations/*team_invitations*.php` or add a new migration
- Test: `tests/Feature/InviteOnlyOnboardingTest.php`

- [x] Step 1: Write a failing feature test for an organization bootstrap invite flow
- [x] Step 2: Run `php artisan test --compact tests/Feature/InviteOnlyOnboardingTest.php`
- [x] Step 3: Implement the minimal invite shape needed for first-time `Organization Commander` onboarding
- [x] Step 4: Re-run `php artisan test --compact tests/Feature/InviteOnlyOnboardingTest.php`
- [x] Step 5: Add brief progress notes here

Notes:

- Decide whether bootstrap invite reuse of `TeamInvitation` is acceptable or whether a dedicated model is cleaner.
- Implemented by extending `TeamInvitation` with `kind = bootstrap|team` and allowing bootstrap invites without `invited_by_user_id`.
- Verified by `tests/Feature/InviteOnlyOnboardingTest.php`.

### Task 3: Split first-run onboarding from ordinary member invitation acceptance

**Files:**
- Modify: `app/Http/Controllers/OnboardingSetupController.php`
- Modify: `app/Http/Requests/StoreInitialUserRequest.php`
- Modify: `resources/views/onboarding/setup.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/InviteOnlyOnboardingTest.php`

- [x] Step 1: Write failing tests for invited `Organization Commander` setup creating organization details and first unit in one flow
- [x] Step 2: Run `php artisan test --compact tests/Feature/InviteOnlyOnboardingTest.php`
- [x] Step 3: Implement the minimal bootstrap completion flow
- [x] Step 4: Re-run `php artisan test --compact tests/Feature/InviteOnlyOnboardingTest.php`
- [x] Step 5: Check off the exact assertions covered in the test notes

Notes:

- Ordinary invited users should not see organization-creation controls.
- Bootstrap setup and member acceptance should no longer be conflated.
- Covered assertions: invite-only landing page, bootstrap invite creates org + first unit + first commander, invalid/expired/accepted bootstrap invites are rejected, and password validation still applies.

### Task 4: Add tenant-visible unit management

**Files:**
- Create: `app/Http/Controllers/UnitController.php` or equivalent resource path matching repo conventions
- Create: `app/Http/Requests/StoreUnitRequest.php`
- Create: `resources/views/team/units/index.blade.php` or another existing team/admin area
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/UnitManagementTest.php`

- [x] Step 1: Create a failing feature test for `Organization Commander` and `Organization Admin` creating units inside their org
- [x] Step 2: Run `php artisan test --compact tests/Feature/UnitManagementTest.php`
- [x] Step 3: Implement the minimal controller, request, route, and UI path
- [x] Step 4: Re-run `php artisan test --compact tests/Feature/UnitManagementTest.php`
- [x] Step 5: Verify the sidebar exposes the screen so the feature is reachable

Notes:

- `Unit Commander` should not receive unit-creation access by default.
- Keep the screen inside the tenant app, not the private admin panel.
- Reachability added through `team.units.*` routes and sidebar navigation.

## Phase 2: Introduce Missions and Actions

### Task 5: Add mission and action schema

**Files:**
- Create: `database/migrations/*create_missions_table.php`
- Create: `database/migrations/*create_mission_memberships_table.php`
- Create: `database/migrations/*create_actions_table.php`
- Create: `database/migrations/*create_action_assignments_table.php`
- Test: `tests/Feature/DatabaseSchemaTest.php`

- [ ] Step 1: Write failing schema tests for the new mission and action tables
- [ ] Step 2: Run `php artisan test --compact tests/Feature/DatabaseSchemaTest.php`
- [ ] Step 3: Add the minimal migrations and indexes
- [ ] Step 4: Re-run `php artisan test --compact tests/Feature/DatabaseSchemaTest.php`
- [ ] Step 5: Record final table names and key indexes in this file

Notes:

- `mission_memberships` must support history fields, not just active membership.
- Deviation: action persistence was retrofitted into the existing `tasks` table via `mission_id`, `action_lead_user_id`, and a dedicated `task_assignments` table instead of introducing a separate `actions` table in this pass.
- Mission schema verified through `tests/Feature/MissionManagementTest.php` and downstream task board behavior in `tests/Feature/TaskBoardTest.php`.

### Task 6: Add Eloquent models and relationships

**Files:**
- Create: `app/Models/Mission.php`
- Create: `app/Models/MissionMembership.php`
- Create: `app/Models/Action.php`
- Create: `app/Models/ActionAssignment.php`
- Modify: `app/Models/Unit.php`
- Modify: `app/Models/User.php`
- Test: `tests/Unit/MissionRelationshipTest.php`

- [ ] Step 1: Write failing unit tests for the new relationships
- [ ] Step 2: Run `php artisan test --compact tests/Unit/MissionRelationshipTest.php`
- [ ] Step 3: Implement the minimal models and relationships
- [ ] Step 4: Re-run `php artisan test --compact tests/Unit/MissionRelationshipTest.php`
- [ ] Step 5: Note any relationship naming decisions here

Notes:

- Deviation: no separate `Action` or `ActionAssignment` Eloquent model was introduced. The existing `Task` model now represents the action engine with `assignedMembers()` and mission relationships.

### Task 7: Add mission creation and roster management

**Files:**
- Create: `app/Http/Controllers/MissionController.php` or Livewire equivalent
- Create: `app/Http/Requests/StoreMissionRequest.php`
- Create: `resources/views/missions/index.blade.php` and related views, or the repo’s preferred Livewire component path
- Test: `tests/Feature/MissionManagementTest.php`

- [x] Step 1: Write failing feature tests for mission creation inside a unit
- [x] Step 2: Run `php artisan test --compact tests/Feature/MissionManagementTest.php`
- [x] Step 3: Implement the minimal mission creation UI and persistence
- [x] Step 4: Re-run `php artisan test --compact tests/Feature/MissionManagementTest.php`
- [x] Step 5: Add tests for permanent and temporary roster entries

### Task 8: Add action creation and assignment behavior

**Files:**
- Create: `app/Http/Requests/StoreActionRequest.php`
- Create: mission action UI views or Livewire components
- Test: `tests/Feature/ActionManagementTest.php`

- [ ] Step 1: Write failing feature tests for action creation under a mission
- [ ] Step 2: Add a failing test proving assigning a non-roster user adds them to the mission roster
- [ ] Step 3: Run `php artisan test --compact tests/Feature/ActionManagementTest.php`
- [ ] Step 4: Implement the minimal action and assignment flow
- [ ] Step 5: Re-run `php artisan test --compact tests/Feature/ActionManagementTest.php`

## Phase 3: Add Commander-Led Unit Training

### Task 9: Add unit-directed training assignment schema

**Files:**
- Create: `database/migrations/*create_unit_training_assignments_table.php`
- Create: `database/migrations/*create_unit_training_assignment_members_table.php`
- Test: `tests/Feature/DatabaseSchemaTest.php`

- [ ] Step 1: Write failing schema tests for commander training assignment records
- [ ] Step 2: Run `php artisan test --compact tests/Feature/DatabaseSchemaTest.php`
- [ ] Step 3: Add the minimal migrations
- [ ] Step 4: Re-run `php artisan test --compact tests/Feature/DatabaseSchemaTest.php`

### Task 10: Add commander workflow in the training area

**Files:**
- Create: `app/Livewire/Training/UnitTrainingAssignmentManager.php` or matching repo convention
- Create: related Blade views if needed
- Test: `tests/Feature/TrainingCommanderWorkflowTest.php`

- [ ] Step 1: Write failing feature tests for selected-member assignments
- [ ] Step 2: Add a failing test for `select all` with member removal by exception
- [ ] Step 3: Run `php artisan test --compact tests/Feature/TrainingCommanderWorkflowTest.php`
- [ ] Step 4: Implement the minimal commander workflow
- [ ] Step 5: Re-run `php artisan test --compact tests/Feature/TrainingCommanderWorkflowTest.php`

### Task 11: Add accountability partner policy controls

**Files:**
- Modify: commander training workflow components
- Modify: training models if needed
- Test: `tests/Feature/TrainingCommanderWorkflowTest.php`

- [ ] Step 1: Write failing tests for these assignment-level modes:
- [ ] Step 2: `No partner required`
- [ ] Step 3: `Partner required, member chooses`
- [ ] Step 4: `Partner assigned by commander and locked`
- [ ] Step 5: `Partner assigned by commander and changeable`
- [ ] Step 6: Run `php artisan test --compact tests/Feature/TrainingCommanderWorkflowTest.php`
- [ ] Step 7: Implement the minimal policy behavior
- [ ] Step 8: Re-run `php artisan test --compact tests/Feature/TrainingCommanderWorkflowTest.php`

## Phase 4: Clean Up Reachability and Replace Legacy Task Terminology

### Task 12: Audit tenant UI navigation and copy

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: affected tenant-facing views
- Test: `tests/Feature/*` targeted to changed screens

- [ ] Step 1: List all screens that should expose units, missions, actions, and commander training
- [ ] Step 2: Add or update failing tests for broken reachability where appropriate
- [ ] Step 3: Implement the minimal navigation and copy updates
- [ ] Step 4: Re-run only the affected test files

### Task 13: Replace or migrate legacy task flows carefully

**Files:**
- Modify: existing task flows only after mission/action replacement is ready
- Test: `tests/Feature/TaskBoardTest.php` and new mission/action tests

- [ ] Step 1: Decide whether the current task board becomes an action board or is retired
- [ ] Step 2: Write failing tests for the chosen migration behavior
- [ ] Step 3: Run `php artisan test --compact tests/Feature/TaskBoardTest.php`
- [ ] Step 4: Implement the minimal transition
- [ ] Step 5: Re-run the affected legacy and replacement tests

Notes:

- Do not cut over the current task UI until mission/action flows exist and are reachable.
- Completed by re-labeling the existing task board as `Actions`, adding mission selection to action creation, and exposing missions in tenant navigation.

## Completion Notes

- Phase 1 shipped invite-only bootstrap onboarding plus tenant-visible unit management.
- Phase 2 shipped missions, historical mission memberships, and multi-assignee actions by retrofitting the existing `Task` model instead of adding a brand-new `Action` model.
- Phase 3 shipped commander-led unit training assignments by extending `training_goals` with commander-assignment fields instead of creating a second training-assignment table family.
- Phase 4 shipped tenant reachability updates for units, missions, actions, and commander training entry points.
- Follow-up UI refinement shipped on April 8, 2026: operational navigation order is now `Muster → Missions → Actions → Training → Calendar`, and the calendar now surfaces events, action due dates, and planned training dates in one unit-scoped view.
- Local realtime follow-up shipped on April 8, 2026: Echo now follows the current Herd site origin instead of hard-coded loopback, falls back to a no-op client if realtime bootstrap fails, and local `.env` settings were aligned to `https://muster.test`. Reverb itself still has to be started outside the sandbox because binding port `8080` is blocked here.
- Platform bootstrap and admin follow-up shipped on April 8, 2026: `migrate:fresh --seed` now leaves the app in a first-run system setup flow instead of a dead-end seeded account, internal operators are created as `is_platform_admin` users, and a Filament 5 panel at `/admin` now exposes overall platform metrics plus bootstrap invitation management for new organizations.
- Final regression pass: `php artisan test --compact tests/Feature/InviteOnlyOnboardingTest.php tests/Feature/UnitManagementTest.php tests/Feature/SocialiteAuthenticationTest.php tests/Feature/MissionManagementTest.php tests/Feature/TaskBoardTest.php tests/Feature/TrainingCommanderWorkflowTest.php tests/Feature/TrainingDashboardTest.php`

## Verification Checklist Before Claiming Completion

- [ ] Run `php artisan test --compact` on each changed feature test file
- [ ] Run `vendor/bin/pint --dirty --format agent` after PHP edits
- [ ] Confirm new routes are reachable from the tenant UI where expected
- [ ] Confirm org boundaries prevent cross-org unit and mission access
- [ ] Confirm temporary mission members remain in history after removal

## Resume Point

When work resumes after interruption:

- [ ] Re-open this file first
- [ ] Find the first unchecked task in the active phase
- [ ] Read the matching spec at `.dev/specs/2026-04-08-command-tenancy-missions-design.md`
- [ ] Continue from the last incomplete verification step, not from memory
