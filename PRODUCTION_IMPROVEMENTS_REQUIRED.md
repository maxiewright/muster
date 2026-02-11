# Production Improvements Required (Muster)

**Date:** February 7, 2026
**Scope:** Codebase + configuration review from repository state

## Executive Summary
The application is close to production-ready at the application layer, but still requires **production configuration, security hardening, and ops readiness** work before launch. The items below are based on what is present in the repository today.

## Critical (Must Do Before Production)

### 1. Lock down security headers with a CSP
- **Current state:** `SecurityHeadersMiddleware` sets basic headers, but no `Content-Security-Policy` is emitted. (`/Users/home/Herd/muster/app/Http/Middleware/SecurityHeadersMiddleware.php`)
- **Required:** Add a CSP (ideally environment-specific) to mitigate XSS risks and unsafe inline scripts.

### 2. Restrict CORS origins for production
- **Current state:** CORS config allows all origins (`allowed_origins` = `*`). (`/Users/home/Herd/muster/config/cors.php`)
- **Required:** Replace wildcard origins with the exact production domains that need access.

### 3. Production environment configuration
- **Current state:** `.env.example` is still tuned for local/dev defaults: SQLite, log mail, log broadcasting. (`/Users/home/Herd/muster/.env.example`)
- **Required:** For production, supply a real `.env` with:
  - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=<prod-url>`
  - A **real database** (`DB_CONNECTION=mysql` or `pgsql` + credentials)
  - A **real mailer** (`MAIL_MAILER=smtp|ses|postmark` + sender identity)
  - A **real broadcaster** (Reverb/Pusher/Ably) if live updates are required
  - `LOG_LEVEL=info` (or stricter) and consider `LOG_STACK=daily`
  - `TRUSTED_PROXIES`, `SESSION_DOMAIN`, `ASSET_URL` if behind a proxy/CDN

### 4. Error tracking in production
- **Current state:** Exception reporting calls Sentry if bound, but no error tracker is installed. (`/Users/home/Herd/muster/bootstrap/app.php`, `/Users/home/Herd/muster/composer.json`)
- **Required:** Choose and install an error tracker (Sentry/Bugsnag/Flare), configure DSN, and verify reporting in staging.

### 5. Storage for uploaded files
- **Current state:** `FILESYSTEM_DISK=local` in `.env.example`. (`/Users/home/Herd/muster/.env.example`)
- **Required:** Use durable storage (S3/Spaces/etc.) for avatars and uploads, and ensure the media library is configured for production storage.

## High Priority (Recommended Before Launch)

### 6. Production-ready caching and queues
- **Current state:** `CACHE_STORE=database`, `QUEUE_CONNECTION=database`. (`/Users/home/Herd/muster/.env.example`)
- **Recommended:** Use Redis (or managed cache/queue) for lower latency and better concurrency. If queues are enabled, run workers under a process manager (Supervisor/systemd).

### 7. Broadcasting readiness
- **Current state:** Default `BROADCAST_CONNECTION=log`. (`/Users/home/Herd/muster/.env.example`)
- **Recommended:** If real-time features are required, set `BROADCAST_CONNECTION=reverb` and provision Reverb credentials and runtime process.

### 8. Log and audit policy
- **Current state:** App is configured for `LOG_STACK=daily` but `LOG_LEVEL=debug` in `.env.example`. (`/Users/home/Herd/muster/.env.example`)
- **Recommended:** Use `LOG_LEVEL=info|warning` in production and define a retention policy.

### 9. Deployment & health checks
- **Current state:** `/health` and `/up` exist. (`/Users/home/Herd/muster/routes/web.php`, `/Users/home/Herd/muster/bootstrap/app.php`)
- **Recommended:** Wire these into load balancer checks and uptime monitoring. Add an automated deploy workflow (or document your manual deploy steps) if not already in place.

## Medium Priority (Strongly Recommended)

### 10. End-to-end UI validation
- **Current state:** No browser/E2E tests are present (Pest tests exist only). (`/Users/home/Herd/muster/tests`)
- **Recommended:** Add a small Dusk or Playwright suite for critical flows: login, standup submission, task creation, calendar event creation.

### 11. UX polish for production confidence
- **Current state:** Several components include loading states, but coverage is inconsistent. (`/Users/home/Herd/muster/resources/views/components`)
- **Recommended:** Audit all user actions for:
  - `wire:loading` feedback on submits
  - Inline validation clarity
  - Empty/zero states on dashboard, calendar, tasks
  - Mobile layout QA for key screens

### 12. Privacy/Compliance housekeeping
- **Current state:** No privacy/cookie notice or data retention policy in repo.
- **Recommended:** Add a minimal privacy policy and data retention stance if the app collects user data in production.

## Low Priority (Nice to Have)

### 13. Performance profiling & budgets
- **Recommended:** Establish performance baselines (page load budgets, database query counts, slow query logging) before scaling.

### 14. Content/SEO basics
- **Recommended:** Add `robots.txt`/`sitemap.xml` if the app is public-facing.

## Notes from Existing Internal Docs
- `PRODUCTION_LEFT_TO_DO.md` and `PRODUCTION_READINESS_REVIEW.md` contain historical review notes, but some items in those documents have already been resolved (e.g., indexes, error pages, security headers). This file reflects current code state.

---

## Quick Go/No-Go Checklist
- [ ] Production `.env` values set (DB, mailer, broadcast, URL, log level)
- [ ] CSP added and CORS restricted to known origins
- [ ] Error tracking installed and verified
- [ ] Durable file storage configured for uploads
- [ ] Health checks and monitoring wired in
- [ ] Queue/cache configured for production load (if applicable)

