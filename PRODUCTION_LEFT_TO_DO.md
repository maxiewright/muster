# Production: What’s Left To Do

This document lists **remaining work** to get Muster production-ready. Items already done in code (migrations, security headers, rate limiting, error pages, etc.) are not listed here.

**Reference:** Full review and checklist → `PRODUCTION_READINESS_REVIEW.md`

---

## Infrastructure & environment

| Item | Notes |
|------|--------|
| **Switch to PostgreSQL or MySQL** | Set `DB_CONNECTION=mysql` (or `pgsql`), then set `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in production `.env`. Run migrations on the new DB. |
| **Configure Reverb for production** | Set `BROADCAST_CONNECTION=reverb`. In `.env`: `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`. Add matching `VITE_REVERB_*` for the frontend. Run Reverb in production (e.g. via Supervisor). |
| **Configure real email** | Set `MAIL_MAILER=smtp` (or `ses`, etc.), `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and **`MAIL_FROM_ADDRESS`** / **`MAIL_FROM_NAME`** to a valid sending identity so verification, password reset, and notifications work. |
| **Queue worker** | Run `php artisan queue:work` in production (e.g. Supervisor or systemd) so jobs and broadcast events are processed. |
| **Cron for scheduler** | Add `* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1` (or your server’s equivalent) so scheduled tasks run. |
| **File storage (e.g. S3)** | For avatars and uploads to persist across deploys, set `FILESYSTEM_DISK=s3` and configure `AWS_*` (or equivalent) in `.env`. |
| **Production `.env`** | Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the real URL, and `LOG_STACK=daily`. Optionally set `SESSION_DOMAIN`, `TRUSTED_PROXIES`, `ASSET_URL` as needed. |

---

## Monitoring & ops

| Item | Notes |
|------|--------|
| **Error tracking** | Install and configure Sentry (or Bugsnag/Flare). The app already reports exceptions to Sentry when bound in the container. |
| **Automated backups** | Use something like `spatie/laravel-backup` (or your host’s backup) for DB and important files. |
| **Laravel Pulse** | Confirm Pulse dashboard route and auth (e.g. `viewPulse` gate) and that Pulse is enabled in production if you use it. |
| **Uptime / health** | Use the existing `GET /health` (and optionally `/up`) in your load balancer or uptime monitor (e.g. Oh Dear, Pingdom). |

---

## Optional improvements

| Item | Notes |
|------|--------|
| **Redis for cache/sessions** | For better performance, set `CACHE_STORE=redis` and optionally `SESSION_DRIVER=redis` and configure Redis. |
| **Query caching** | Add caching for expensive or rarely changing data (e.g. leaderboards, badge list) with appropriate TTLs. |
| **Form Request validation** | Replace inline validation in Livewire with dedicated Form Request classes where it improves maintainability. |
| **Loading states** | Add `wire:loading` (or similar) on Livewire forms/actions so users get clear feedback. |
| **Browser / E2E tests** | Add Pest Dusk (or similar) tests for critical flows (e.g. standup wizard, task creation). |
| **Deployment automation** | Add CI/CD (e.g. GitHub Actions), deploy script, or Forge/Vapor config as needed. |
| **Deployment docs** | Document deploy steps (env, migrations, queue, cron, assets) in e.g. `DEPLOYMENT.md` or in the main README. |

---

## Pre-launch checklist

- [ ] Production DB (PostgreSQL/MySQL) configured and migrated
- [ ] Reverb (or chosen broadcast driver) configured and running
- [ ] Real mail driver and `MAIL_FROM_*` set
- [ ] Queue worker running (Supervisor or equivalent)
- [ ] Cron for `schedule:run` configured
- [ ] `APP_DEBUG=false` and `APP_ENV=production` in production `.env`
- [ ] Error tracking (e.g. Sentry) configured and tested
- [ ] Backups configured and tested once
- [ ] Health/uptime checks pointing at `/health` or `/up`

---

**Last updated:** February 2026
