# Manual UI QA Checklist

**Environment**
- Browser: Chrome (latest), Safari (latest)
- Viewports: 320px, 375px, 768px, 1024px
- User: Test account with tasks, events, and standups

---

## 1. Global UI & Theme
1. Toggle theme (Light/Dark/System).
   - Expected: Theme changes immediately and persists across pages.
2. Hover over links/buttons across pages.
   - Expected: Cursor changes to pointer.
3. Check tap targets on mobile.
   - Expected: Primary actions and icon buttons are easy to tap.

---

## 2. Layouts (App Shell)
1. Open the app layout (Dashboard).
2. On mobile width, open sidebar toggle.
   - Expected: Toggle is easy to tap, sidebar opens correctly.
3. Verify padding consistency across main pages.
   - Expected: Content is not cramped or overly spaced.

---

## 3. Calendar
1. Open Calendar on mobile (320–375px).
   - Expected: Agenda list view is shown (not full month grid).
2. Open Calendar on desktop.
   - Expected: Month grid appears with Monday as the first day.
3. Confirm today’s highlight aligns with actual day.
4. Open Create Event modal.
   - Expected: Start/End fields do not overlap, modal fits screen without inner scroll.
5. Add event and verify it appears in list/grid.

---

## 4. Tasks
1. Open Tasks board on mobile.
   - Expected: Columns scroll horizontally, each column width is usable.
2. Check task card actions (complete, menu, start).
   - Expected: Visible and tappable on mobile (no hover dependency).
3. Open task detail modal.
   - Expected: Content readable without internal scrollbar.
4. Open Create Task modal.
   - Expected: Fields stack properly on mobile.

---

## 5. Standups
1. Open Standup Wizard.
2. Step 2: Quick Create Task input.
   - Expected: Input matches other field styles and focus ring is consistent.
3. Step 2: “Today’s Tasks” list.
   - Expected: You can drag a task by grabbing its row (entire row is draggable).
4. Step 3: Blockers + Mood.
   - Expected: Focus styles are consistent and grid fits mobile.

---

## 6. Settings
1. Profile page.
   - Expected: Avatar section stacks on mobile; buttons align.
2. Password page.
   - Expected: Save button full width on mobile, consistent spacing.
3. Appearance page.
   - Expected: Segmented control aligns; save button is consistent.
4. Two‑Factor modal.
   - Expected: Modal fits screen without inner scroll; controls usable.

---

## 7. Auth Flows
1. Login/Register/Forgot Password/Reset Password.
   - Expected: Buttons full width and tappable on mobile.
2. 2FA Challenge.
   - Expected: Toggle between recovery/OTP is focusable and keyboard‑accessible.
3. Verify Email page.
   - Expected: Both “Resend” and “Log out” buttons are tappable and aligned.
