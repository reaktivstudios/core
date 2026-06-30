You are assisting on a WordPress codebase running on **WordPress VIP** (or VIP-equivalent). Every suggestion must be production-grade: secure, performant, accessible, VIP-compliant. Match the surrounding code's conventions before introducing new patterns. Never silently weaken security, escaping, sanitisation, capability checks, or nonces. When in doubt, ask.

## How to behave

- **Be proactive about quality.** When you make a requested change, also flag refactoring opportunities you see in the same file or nearby (long functions, duplicated logic, tight coupling, missing abstractions, dated PHP). Provide a brief rationale; the developer will decide whether to apply.
- **Prefer fewer, clearer suggestions over many speculative ones.** When in doubt, ask a clarifying question instead of guessing.
- **Prefer line breaks** Instead of overly verbose condensed paragraphs, please consider breaking into new paragraphs to enhance readability. 

## Environment

- **WordPress** 6.4+, **PHP** 8.1+ (typed properties, readonly, enums, match, nullsafe, first-class callables), **JS** ES2022+ with React 18 and `@wordpress/scripts`, **CSS** Sass/PostCSS mobile-first, BEM.
- **VIP assumptions:** read-only filesystem outside `/tmp`, persistent object cache, page cache in front of app, no runtime shell.

## PHP & WordPress

- Pass **WordPress-VIP-Go** + **WordPress-Extra** PHPCS rulesets and the project's PHPStan/Psalm level. Add types rather than suppress.
- Prefix everything project-specific (`acme_`, `acme/`). One class per file, PSR-4 standards.
- Files should be setup in a `inc` folder in the repo and classes can be loaded via an autoloader and optionally inside of a `Core.php` class
- WordPress actions and hooks should be run inside of a separate `init` or `run` function in a class, never in `__construct`
- Extend via hooks; never patch core or third-party plugins. Add `do_action`/`apply_filters` extension points to reusable infrastructure.
- **Queries:** always `$wpdb->prepare()` for any interpolated value. Prefer `WP_Query`/`get_posts()` with `no_found_rows`, `update_post_meta_cache`, `update_post_term_cache` set to `false` when not needed. Never `posts_per_page => -1` on user-facing queries. Never `query_posts()`. Avoid heavy `meta_query`/`tax_query` on hot paths — denormalise or index.

## VIP rules

- No filesystem writes outside `/tmp`. Use the media library / object storage.
- HTTP: `vip_safe_wp_remote_get()` / `wp_safe_remote_*` with explicit timeouts and caching. Never `file_get_contents()` on a URL.
- Cache expensive work via `wp_cache_*`; use versioned transients with TTLs for cross-request data.
- Cron jobs must be re-entrant and chunked.

## Performance

- Target single-digit DB queries on the critical path. Flag any query inside a loop (n+1) — pre-fetch, batch, or warm post-meta caches.
- Cache anything computed more than once per request or expensive to repeat. Pick keys that include all inputs; invalidate on writes. Make sure to bust cache.
- Enqueue assets via `wp_enqueue_script`/`wp_enqueue_style` with content-hashed versions. Defer/async non-critical JS. Inline only critical CSS. Conditional enqueues — no editor assets on the front end, no front-end assets in admin.
- Images: rely on core `srcset`/`sizes`; `loading="lazy"` + `decoding="async"` below the fold; explicit `width`/`height` to prevent CLS.
- Server-rendered blocks must be cheap; cache expensive helpers.
- No synchronous external API calls on render paths — cache + serve stale on failure.

## Accessibility (WCAG 2.1 AA)

- Semantic HTML first; reach for ARIA only when semantics genuinely fall short, never to *replace* them.
- Every interactive element is keyboard-reachable with a visible focus state (no `outline: 0` without an equivalent `:focus-visible`).
- Forms: associated `<label>` for every input, errors via `aria-live`/`role="alert"`, no placeholder-as-label.
- Images: descriptive `alt` for meaningful, `alt=""` for decorative, `<title>` + `role="img"` on meaningful SVG.
- Color contrast 4.5:1 body / 3:1 large text and UI; never color alone for meaning.
- One `<h1>` per page, no skipped levels. Skip-link to main on every template.
- Respect `prefers-reduced-motion`. Use `.screen-reader-text` (not `display: none`) for SR-only text.
- Manual keyboard + screen-reader pass before UI is "done"; automated checks catch ~30%.

## Gutenberg / block editor

- `block.json` is the source of truth; register via `register_block_type( __DIR__ . '/build/my-block' )`.
- Builds will happen at the root via a `build.js` file.
- Version numbers should be incremented for a block whenever a block is updated
- Use `@wordpress/*` packages (`@wordpress/element`, `components`, `data`, `i18n`); don't import React or lodash directly. Functional components + hooks only.
- Use the **Interactivity API** for stateful front-end behavior, not jQuery or ad-hoc DOM scripts.
- Server-render (`render_callback`) when output depends on dynamic data; static blocks save markup. **Always escape on output**, even in server renders.
- Strings via `__()`/`_x()`/`_n()` with the project text domain; `wp_set_script_translations` for JS.

## Security

**1. Taint analysis.** Trace untrusted input to dangerous sinks.

- Superglobals: `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES`, `$_SERVER`
- WordPress inputs: `get_option()`, `get_post_meta()`, `get_user_meta()`, `get_query_var()`
- Database reads that could contain attacker-controlled data

*Sinks:* SQL (`$wpdb->query`/`get_results`/`get_var`, misused `prepare`); output (`echo`, `print`, `printf`, `_e`, missing/wrong `esc_*`); filesystem (`file_get_contents`, `file_put_contents`, `include`/`require`, `fopen`); execution (`exec`, `system`, `passthru`, `shell_exec`, `popen`, `proc_open`); `unserialize`/`maybe_unserialize` on user input; unvalidated `wp_redirect`/`wp_safe_redirect`; `wp_mail` header injection.

**2. WordPress checks.** Missing `wp_verify_nonce` / `check_ajax_referer` on state changes; missing `current_user_can` on privileged ops; queries bypassing `$wpdb->prepare`; unrestricted uploads; unauthorised `update_option`; REST routes without `permission_callback`; shortcodes with unsanitised attributes.

**3. Logic & auth flaws.** Wrong-capability checks; nonces checked *after* the sensitive op; file race conditions; IDOR (no ownership check); password-reset flaws; privilege escalation via user meta; hardcoded credentials/keys/secrets.

**4. Deps & config.** Weak hashing/randomness for security (`md5`/`sha1` passwords, `rand`/`mt_rand` for tokens — use `wp_rand`/`random_bytes`); unsafe PHP config assumptions; production debug residue (`var_dump`, `print_r`, sensitive `error_log`).

## Refactoring — be proactive

Surface refactor opportunities with a one-line rationale; don't block the requested change. Watch for: long functions/classes (>50 lines / >300 lines), duplication, tight coupling (suggest DI), primitive obsession (value object/enum), god hooks (split callbacks), dated PHP (`array()` → `[]`, untyped → typed, `if/elseif` chains → `match`), magic numbers/strings (constants/enums), misleading or inconsistent names, missing layer abstractions, comments that explain *what* (rename instead), dead code, untestable code reaching into globals. Prefer the smallest improving change; raise larger refactors as follow-ups.

## Documentation

- PHPDoc on all public functions, methods, hooks, classes, constants. Summary line, blank line, description,`@param`, `@return` Document custom hooks WordPress-style with full `@param` per arg.
- Inline comments explain **why.**
- If the PR does not include testing instructions, prompt the code submitter.
- If code is introducing a new feature or larger change, indicate to the developer that they may need to update documentation in Notion

## Git & PRs

- Branches: `feature/ACME-123-slug`, `fix/ACME-456-slug`, `chore/slug`. No direct pushes to `main`/`develop`.
- Pull request branches are to be named: **{*type}/{YYYY-MM-DD}-{branch-description}***

```
ex:feature/2022-04-26-add-button-to-contact-form
ex:refactor/2024-11-25-language-from-project-task
```

- The **types** of pull request branches are as follows. Most often, it will be *refactor*, *feature*, or *fix*.

```
❯refactor: A code change that neither fixes a bug nor adds a feature
❯feature: A new feature
❯fix: A bug fix
❯hotfix: An urgent fix that directly corrects a live issue in production
❯docs: Documentation only changes
❯style: Changes that do not affect the meaning of the code (white-space, formatting, missing semicolons, etc)
❯test: Adding missing or correcting existing tests
❯chore: Changes and updates to the build process, plugins, or auxiliary tools and libraries such as documentation generation
```

- **PR ready when:** does one thing, description covers what/why/how-to-test plus screenshots for UI, no debug spam or commented-out code, no new `phpcs:ignore` without justification, docs/changelog updated, migrations idempotent and reversible.
- **Reviewer pass:** security analysis applied, performance has caching/n+1 story, a11y keyboard-tested, VIP-safe, no removed public hooks without deprecation.

## When unsure

Ask. Follow the codebase if it contradicts these instructions and surface the inconsistency. If a request would weaken security or break VIP rules, refuse and offer a compliant alternative.
