# Command Tenancy, Missions, and Training Design

## Status

- [x] Brainstorming complete
- [x] Design approved in chat
- [x] Spec written
- [x] Implementation started

## Goal

Reshape the app around organization-scoped command structures, unit-level missions/actions, and commander-led training workflows so the product can support separate services such as the Trinidad and Tobago Regiment, Coast Guard, and Air Guard without cross-org leakage.

## Summary

The app should support two clear layers:

- A private internal provisioning layer for Muster operators
- A tenant-facing command layer for each organization

Tenant operations should be modeled as:

- `Organization`
- `Unit`
- `Mission`
- `Action`

Training remains a separate domain from missions, but gains a commander workflow for unit-directed training assignments.

## Provisioning and Tenancy

### Private Provisioning Layer

- A private internal admin panel provisions new organizations.
- The panel is only for internal Muster operators.
- It should not be visible to tenant users.
- Provisioning happens by issuing a bootstrap invite to the first `Organization Commander`.
- The bootstrap invite does not pre-create units.

### Tenant Setup Flow

- The invited `Organization Commander` signs in to the main app.
- On first-run setup, they create the organization details and the first unit in one flow.
- After that, all administration remains inside that organization.
- No tenant-facing global super-admin should exist inside the main app.

### Organization Boundaries

- Each service or top-level command is its own `Organization`.
- Users and units stay inside their organization boundary.
- Cross-organization management is not available in the tenant app.

## Roles and Authority

### Organization Roles

- `Organization Commander`
- `Organization Admin`

Both roles may create units inside their own organization.

### Unit and Mission Roles

- `Unit Commander`
- `Mission Commander`
- `Action Lead`

Notes:

- `Unit Commander` manages unit-level work and readiness.
- `Unit Commander` does not create units by default.
- `Mission Commander` is scoped to a mission.
- `Action Lead` is scoped to an action.
- A user may hold multiple responsibilities at the same time.
- A `Mission Commander` may also act as an `Action Lead`.

## Operational Domain Model

### Hierarchy

- An `Organization` has many `Units`
- A `Unit` has many `Missions`
- A `Mission` has many `Actions`

### Missions

- A mission belongs to exactly one unit.
- A mission has one `Mission Commander`.
- A mission has a mission-level roster.
- Mission roster members are drawn from unit members.
- Mission roster membership must be historical, not a disposable pivot.

### Mission Membership History

Mission roster records should support:

- `permanent` membership
- `temporary` membership
- who added the member
- who removed the member
- when the member joined
- when the member left

Manual removal is required:

- Temporary members are not auto-removed when they finish their assigned work.
- Leads remove temporary members manually.

History must remain queryable so a similar mission can be spun up later with a proven team.

### Actions

- Every action belongs to exactly one mission.
- Every task-like work item in the future model should be an action under a mission.
- An action has one `Action Lead`.
- An action can have many assigned members.
- A person outside the current mission roster may still be assigned to an action.

When assigning someone outside the mission roster:

- The system adds them to the mission roster.
- They may be added as temporary members when appropriate.

## Naming Direction

The approved command-oriented terminology is:

- `Mission`
- `Action`

This replaces the previously discussed `Operation`/`Task` option.

## Training Domain

Training remains separate from missions.

### Personal Training

- Members can continue entering their own training.
- Members can continue choosing an accountability partner.

### Unit-Directed Training

- Unit-directed training lives in the training area as a separate commander workflow.
- A `Unit Commander` can assign training to specifically selected members.
- The UI should support `select all` with removal by exception.
- This workflow is separate from mission planning.

### Accountability Partner Rules

For unit-directed training assignments, accountability partner policy is assignment-level configuration.

Supported modes:

- No partner required
- Partner required and member chooses
- Partner assigned by commander

If the commander assigns a partner:

- The commander can mark the partner as `locked`
- Or allow the member to change the assigned partner

If the commander does not assign a partner:

- They may still require the member to choose one before beginning training

## Recommended Data Boundaries

The implementation should introduce or reshape records along these lines:

- `organizations`
- `units`
- `missions`
- `mission_memberships`
- `actions`
- `action_assignments`
- bootstrap invite records for first-time organization setup
- unit-directed training assignment records

`mission_memberships` should be treated as a historical record rather than a simple many-to-many pivot.

## Recommended Rollout Order

1. Build the private provisioning flow for new organizations.
2. Reshape first-run onboarding so the first invited `Organization Commander` creates organization details and the first unit together.
3. Add org-scoped admin permissions for unit creation.
4. Introduce `Mission` and `Action`.
5. Add mission roster history with permanent and temporary membership.
6. Add commander-led unit training assignments.
7. Add reuse features later, such as spinning up new missions from prior mission rosters.

## Progress Notes

- This spec reflects the approved design from the April 8, 2026 session.
- The implementation plan is tracked separately in `.dev/plans/2026-04-08-command-tenancy-missions-implementation-plan.md`.
- The delivered implementation keeps the approved tenant model, with two pragmatic retrofits:
  - action persistence is layered onto the existing `tasks` table
  - commander-led unit training assignments are layered onto `training_goals`
