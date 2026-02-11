# UI/UX Mobile-First Review and Improvement Report (Livewire 4 + Flux UI)

**Date:** February 7, 2026
**Scope:** App + Auth views and modals (public welcome page excluded)

## Executive Summary
**Overall mobile-first readiness:** Good foundation with Flux UI and Tailwind v4, but several mobile and accessibility gaps remain. Primary risks are forced dark mode, dense calendar grid on small screens, and inconsistent mobile stacking in multi-column forms and modals.

**Top 5 P0 items**
1. **Theme handling conflict**: Remove hardcoded `class="dark"` on `<html>` so `@fluxAppearance` can control theme. Otherwise, users cannot switch to light/system.  
   Files: `/Users/home/Herd/muster/resources/views/layouts/app/header.blade.php`, `/Users/home/Herd/muster/resources/views/layouts/app/sidebar.blade.php`, `/Users/home/Herd/muster/resources/views/layouts/auth/simple.blade.php`, `/Users/home/Herd/muster/resources/views/layouts/auth/card.blade.php`, `/Users/home/Herd/muster/resources/views/layouts/auth/split.blade.php`
2. **Calendar grid density on mobile**: 7-column month grid is too cramped < 375px. Provide a mobile agenda/list or horizontally scrollable day cards.  
   File: `/Users/home/Herd/muster/resources/views/components/calendar/⚡calendar-view/calendar-view.blade.php`
3. **Modal overflow on small screens**: Several modals lack consistent `max-h` + internal scroll; content can overflow on small devices.  
   Files: `/Users/home/Herd/muster/resources/views/components/task/⚡task-detail-modal/task-detail-modal.blade.php`, `/Users/home/Herd/muster/resources/views/components/task/⚡create-task-modal/create-task-modal.blade.php`, `/Users/home/Herd/muster/resources/views/components/calendar/⚡create-event-modal/create-event-modal.blade.php`, `/Users/home/Herd/muster/resources/views/livewire/settings/two-factor.blade.php`
4. **Two-column form layouts without mobile fallback**: Split fields must stack on small screens.  
   Files: `/Users/home/Herd/muster/resources/views/components/task/⚡create-task-modal/create-task-modal.blade.php`, `/Users/home/Herd/muster/resources/views/components/calendar/⚡create-event-modal/create-event-modal.blade.php`
5. **Tap target size for icon-only actions**: Icon-only buttons and navigation controls should meet 44px minimum tap size.  
   Files: `/Users/home/Herd/muster/resources/views/components/calendar/⚡calendar-view/calendar-view.blade.php`, `/Users/home/Herd/muster/resources/views/components/standup/⚡standup-board/standup-board.blade.php`, `/Users/home/Herd/muster/resources/views/components/task/⚡task-card/task-card.blade.php`, `/Users/home/Herd/muster/resources/views/components/task/⚡task-detail-modal/task-detail-modal.blade.php`

## Global UI/UX Improvements

**P0: Theme handling conflict**  
File references above.  
**Issue:** `<html class="dark">` forces dark mode and blocks user preference + `@fluxAppearance`.  
**Actionable fix:** Remove the hardcoded `dark` class on `<html>` in all layouts. Rely on `@fluxAppearance` and Flux’s internal theme handling.  
**Outcome:** Light/dark/system preferences work correctly across devices.

**P1: Form grid stacking**  
Files: task create/edit modal, event create/edit modal.  
**Issue:** `grid grid-cols-2` causes cramped fields on 320px–375px.  
**Actionable fix:** Use `grid grid-cols-1 sm:grid-cols-2` to stack on mobile, then split at `sm`.  
**Outcome:** Mobile forms are readable and don’t force pinch/zoom.

**P1: Modal size + internal scroll**  
Files: task detail, create task, create event, two-factor setup modal.  
**Issue:** Some modals lack `max-h` and internal scrolling, causing viewport overflow.  
**Actionable fix:** Apply consistent modal container classes like `max-h-[80vh] overflow-y-auto` to inner content and ensure adequate padding.  
**Outcome:** Modals remain usable on smaller screens without off-screen buttons.

**P1: Tap targets and touch ergonomics**  
Files: calendar nav, standup nav, task controls.  
**Issue:** Icon-only controls risk sub-44px targets.  
**Actionable fix:** Add padding and size variants (Flux `size="sm"` with padding utility) or wrap icons in buttons with `min-h-[44px] min-w-[44px]`.  
**Outcome:** Better usability on phones, fewer missed taps.

**P2: Consistent action hierarchy**  
Files: multiple modals and cards.  
**Issue:** Primary and secondary actions are inconsistent across screens.  
**Actionable fix:** Standardize: primary action right-aligned, secondary ghost or outline, destructive isolated to left or separated block.  
**Outcome:** Predictable actions reduce user error.

## Per-Screen Improvements (Prioritized)

### Layouts (App + Auth)
**P0**  
File: `/Users/home/Herd/muster/resources/views/layouts/app/header.blade.php`  
**Issue:** `<html class="dark">` forces dark theme.  
**Fix:** Remove the class; use `@fluxAppearance` only.  
**Outcome:** Theme switching works as expected.

**P1**  
File: `/Users/home/Herd/muster/resources/views/layouts/app/sidebar.blade.php`  
**Issue:** Mobile menu uses sidebar and header separately; icon-only controls may be small.  
**Fix:** Ensure sidebar toggle and dropdown profile meet 44px tap target, add padding utilities.  
**Outcome:** Mobile navigation is easier to use.

**P2**  
File: `/Users/home/Herd/muster/resources/views/layouts/auth/split.blade.php`  
**Issue:** Large hero left panel hidden on mobile but overall content width is constrained to `sm:w-[350px]`, which can feel narrow on modern devices.  
**Fix:** Consider `sm:w-[360px] md:w-[400px]` or `max-w-sm` at small and increase at md.  
**Outcome:** Auth screens feel less cramped without losing focus.

### Dashboard
**P1**  
File: `/Users/home/Herd/muster/resources/views/components/⚡dashboard/dashboard.blade.php`  
**Issue:** Dense content blocks can stack well, but badges and action buttons should ensure minimum tap size.  
**Fix:** Ensure badges and buttons use `min-h-[44px]` when interactive and shorten text for mobile.  
**Outcome:** Faster touch interactions, fewer missed taps.

**P2**  
File: `/Users/home/Herd/muster/resources/views/components/⚡dashboard/dashboard.blade.php`  
**Issue:** Standup task summaries in list form may overflow or wrap awkwardly on smaller screens.  
**Fix:** Add `line-clamp-2` for task lists and reduce inline counts to prevent wrap.  
**Outcome:** Cleaner mobile reading experience.

### Standups (Board + Form)
**P1**  
File: `/Users/home/Herd/muster/resources/views/components/standup/⚡standup-board/standup-board.blade.php`  
**Issue:** Date navigation uses icon-only controls without ensured tap size.  
**Fix:** Wrap nav buttons in padding or apply `min-h-[44px] min-w-[44px]`.  
**Outcome:** Usable navigation on mobile.

**P1**  
File: `/Users/home/Herd/muster/resources/views/components/standup/⚡standup-form/standup-form.blade.php`  
**Issue:** This form uses custom HTML and dense content; spacing is good but controls are not fully Flux-aligned.  
**Fix:** Replace custom button styles and text inputs with Flux components where available for consistency and accessibility.  
**Outcome:** Consistent form behavior and styling across the app.

**P2**  
File: `/Users/home/Herd/muster/resources/views/components/standup/⚡standup-form/standup-form.blade.php`  
**Issue:** Stepper is visually dense on small screens.  
**Fix:** Reduce step labels to short text or move to a stacked vertical stepper on `<sm`.  
**Outcome:** Better readability and flow on mobile.

### Tasks (Board, Columns, Card, Modals)
**P1**  
File: `/Users/home/Herd/muster/resources/views/components/task/⚡task-board/task-board.blade.php`  
**Issue:** Horizontal scroll board is good on mobile, but column width is fixed at `280px` which can create awkward scroll for 320px screens.  
**Fix:** Use `w-[260px] sm:w-[280px]` or `w-[85vw]` on smallest screens.  
**Outcome:** Smoother scrolling on small devices.

**P1**  
File: `/Users/home/Herd/muster/resources/views/components/task/⚡create-task-modal/create-task-modal.blade.php`  
**Issue:** Two-column grid does not stack on mobile.  
**Fix:** `grid grid-cols-1 sm:grid-cols-2`.  
**Outcome:** Mobile form usability.

**P1**  
File: `/Users/home/Herd/muster/resources/views/components/task/⚡task-detail-modal/task-detail-modal.blade.php`  
**Issue:** Modal content can exceed viewport height.  
**Fix:** Add `max-h-[80vh] overflow-y-auto` to the modal content wrapper and ensure close button has 44px hit area.  
**Outcome:** Modal usable on smaller screens.

**P2**  
File: `/Users/home/Herd/muster/resources/views/components/task/⚡task-card/task-card.blade.php`  
**Issue:** Quick action buttons are small and rely on hover states.  
**Fix:** Increase hit area and show a minimal action row on mobile (always visible).  
**Outcome:** Mobile users can access actions without hover.

### Calendar (View + Modal)
**P0**  
File: `/Users/home/Herd/muster/resources/views/components/calendar/⚡calendar-view/calendar-view.blade.php`  
**Issue:** 7‑column grid is too dense on mobile.  
**Fix:** Provide a mobile agenda/list view at `<sm`, or a horizontally scrollable day-card layout with a single column.  
**Outcome:** Calendar becomes usable on phones.

**P1**  
File: `/Users/home/Herd/muster/resources/views/components/calendar/⚡create-event-modal/create-event-modal.blade.php`  
**Issue:** Two-column date/time fields do not stack on small screens.  
**Fix:** `grid grid-cols-1 sm:grid-cols-2`.  
**Outcome:** Mobile form usability.

### Gamification
**P2**  
File: `/Users/home/Herd/muster/resources/views/components/gamification/⚡gamification/gamification.blade.php`  
**Issue:** Badge grids are dense on small screens; long names can wrap.  
**Fix:** Use `grid-cols-3 sm:grid-cols-4` with shorter labels or `line-clamp-1` for names.  
**Outcome:** Cleaner badge presentation on mobile.

### Settings (Profile, Password, Appearance, 2FA)
**P1**  
File: `/Users/home/Herd/muster/resources/views/components/settings/layout.blade.php`  
**Issue:** Sidebar nav is narrow and collapses below 768px; content max width is `max-w-lg` which can feel narrow on tablet.  
**Fix:** Consider `max-w-xl` on `md` for settings content.  
**Outcome:** More balanced layout on tablets.

**P1**  
File: `/Users/home/Herd/muster/resources/views/livewire/settings/two-factor.blade.php`  
**Issue:** 2FA setup modal content is large and dense.  
**Fix:** Ensure internal scrolling and consolidate copy on mobile.  
**Outcome:** Less scrolling and better focus on primary action.

**P2**  
File: `/Users/home/Herd/muster/resources/views/livewire/settings/profile.blade.php`  
**Issue:** Profile photo upload block is wide; file input and buttons can wrap awkwardly.  
**Fix:** Stack buttons on `<sm`, keep avatar left with `sm:flex-row`.  
**Outcome:** Better mobile alignment.

### Auth (Login/Register/Reset/Verify/2FA/Confirm)
**P1**  
Files: `/Users/home/Herd/muster/resources/views/livewire/auth/*`  
**Issue:** Some flows lack explicit loading feedback on submit (e.g., login, reset).  
**Fix:** Add `wire:loading` states or Flux loading variants where Livewire is used; ensure `min-h-[44px]` buttons.  
**Outcome:** Clear feedback and improved mobile usability.

**P2**  
File: `/Users/home/Herd/muster/resources/views/livewire/auth/two-factor-challenge.blade.php`  
**Issue:** OTP input and recovery input transitions are clean, but the toggle link is small.  
**Fix:** Increase toggle hit area or use Flux button/link styling.  
**Outcome:** More usable on mobile.

## Test Scenarios (Validation Checklist)
- 320px, 375px, 768px, 1024px widths on all screens.
- Confirm no horizontal scroll on primary content.
- Confirm all interactive controls meet 44px minimum tap area.
- Confirm modals are fully usable with internal scrolling.
- Confirm theme switching works with light/dark/system.

