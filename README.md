# The Pond
A wordpress site that uses learndash/memberpress to provide the most efficient hockey skills courses in the world.

## Current migration: BuddyBoss Theme + the existing Pond portal

The prepared [BuddyBoss child theme](wp-content/themes/buddyboss-theme-child-1.0.0)
lets BuddyBoss own the member-site header, footer, mobile navigation, community
and LearnDash layouts. This supersedes the hybrid/ReadyLaunch instructions below.
**BuddyBoss Child is active in production.** Its production folder is
`buddyboss-theme-child-1.0.0`. The complete manifest below is for initial deployment;
use each refinement's smaller upload list for the already migrated site.

### Preserved functionality

- The dashboard keeps its course cards, LearnDash profile/progress, challenges,
  Move Makers, routines and Skills Vault table, filters, ratings and resource dialogs.
  Legacy content styles are scoped to `.pond-portal`, not BuddyBoss chrome.
- Firebase/miniOrange login, MemberPress registration, password-reset, account,
  checkout and subscription overrides retain their existing URLs and form hooks.
  Firebase handlers and challenge scores now work whether or not another script
  has already initialized the Firebase app. WordPress jQuery is not replaced.
- Required content types, taxonomies, ACF definitions, account/avatar hooks and
  shortcodes are loaded from [the integration's portal module](wp-content/plugins/thepond-community/portal)
  only when BuddyBoss is the parent theme. Melting Pot remains independently usable.
- Native LearnDash content tabs receive the existing ACF video/image/audio,
  Vimeo jump links, goals, practice tips/mistakes, skill links and downloads/resources.
  Both MemberPress authorization and LearnDash course access are required for
  this protected material. Native progression/drip gating determines whether the
  content tabs render; the adapter does not replace LearnDash templates, enrollment,
  navigation or completion controls.
- Challenges retain their score controls and Firebase collection. The Challenge
  Score plugin no longer overrides the migrated child template on BuddyBoss.
  Routines, content-library browsing, skill pages, My Content and support remain available.
- Existing menu items are reused, including dashboard anchors, search, account
  and logout. Logout clears Firebase and then uses WordPress's nonce-protected
  logout URL. Custom account-menu destinations also appear in the native mobile menu.
- The public homepage remains its existing standalone marketing template, including
  membership-selection links. It deliberately does not acquire BuddyBoss's portal shell.
  Keep **both old Melting Pot themes installed**: their existing static assets,
  placeholder images and the standalone landing-page source are reused.
  No vendor parent theme or complete third-party plugin needs uploading/tracking.
- ReadyLaunch is forced off with BuddyBoss Theme so it cannot compete with native
  community or LearnDash rendering. The old hybrid behavior remains available on
  Melting Pot. Native BuddyBoss community templates are retained after Elementor's
  template filter, preventing Canvas from stripping the shell.

### FTP upload manifest

Upload to the **same relative production paths**, overwriting only these files.
Do not activate BuddyBoss Child until all three groups are uploaded.

If the original migration files are already uploaded, the **1.2.1 recovery update**
requires overwriting only `wp-content/plugins/thepond-community/thepond-community.php`.
It fixes recursive login URL generation on both themes: WordPress's `is_login()`
calls `wp_login_url()` and must not run inside the `login_url` filter. The integration
now uses WordPress's `$pagenow` context to preserve core login and password-reset URLs.
After recovery, reactivate Platform first, then the custom integration and BuddyBoss
LearnDash, and verify the Melting Pot frontend before activating BuddyBoss Child.

For the subsequent dashboard-width refinement, overwrite only
`wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css`.
The dashboard uses the full available content width instead of the parent theme's
1200px cap; other native pages retain their layouts. The stylesheet's existing
file-modification-time versioning refreshes its browser cache after upload.

1. `wp-content/plugins/thepond-community/thepond-community.php` and **all nine files
   in `wp-content/plugins/thepond-community/portal/`**.
   Do **not** upload `tests/`.
2. These changed Challenge Score files:
   - `wp-content/plugins/challenge-score/filters.php`
   - `wp-content/plugins/challenge-score/enqueue.php`
   - `wp-content/plugins/challenge-score/js/score-management.js`
3. These files/folders inside `wp-content/themes/buddyboss-theme-child-1.0.0/`:
   - `functions.php`, `style.css`, `home.php`, `page_Elementor.php`, `firebase-login.php`
   - `challenges.php`, `routines.php`, `move-makers.php`
   - `single-content-library.php`, `archive-content-library.php`, `single-skills.php`
   - `inc/pond.php`
   - `assets/css/custom.css`, `assets/css/pond-grid.css`, `assets/css/pond-legacy.css`
   - `assets/js/custom.js`, `assets/js/firebase.js`, `assets/js/pond-dashboard.js`
   - all files in `members-templates/`, `memberpress/`, and `template-parts/`

The BuddyBoss vendor parent, screenshot, languages and downloaded stock extras
remain ignored by Git. Only the customized child and custom integration code are
tracked. Do not delete the old themes, vendor plugins, memberships, lessons or media.

### WordPress configuration after upload

The authorized browser-assisted migration can perform these settings changes:

1. Ensure BuddyBoss Platform, BuddyBoss LearnDash, MemberPress, miniOrange/Firebase,
   ACF, Elementor, Challenge Score, Skill Competency Slider and The Pond Community
   Integration remain active. Verify the homepage, login, dashboard and lesson on
   Melting Pot before changing the theme.
   Do not enable either direction of LearnDash/social-group synchronization.
2. Turn ReadyLaunch off and activate **BuddyBoss Child**, folder
   `buddyboss-theme-child-1.0.0`. The integration also enforces ReadyLaunch off.
3. Confirm LearnDash **Active Template = LearnDash 3.0** and start with
   **Focus Mode off** to retain the shared BuddyBoss member navigation.
   Do not change course access modes, billing, enrollments or existing progress.
4. Assign the existing menus under Appearance > Menus > Manage Locations:

   | BuddyBoss/Pond location | Existing menu |
   | --- | --- |
   | Header Menu - Logged in / Mobile Menu - Logged in | Member Main Menu (24) |
   | Header Menu - Logged out / Mobile Menu - Logged out | primary-menu (21) |
   | Profile Dropdown | Member Top Menu (23) |
   | Pond Footer - Logged in | Member Footer Menu (25) |
   | Pond Footer - Logged out | secondary-menu (22) |

   If BuddyPanel is enabled, use the corresponding existing main menus there too.
   Keep all existing menu destinations/hierarchy. Add native Community links as
   appropriate; do not assign `/members/` to BuddyBoss because it is an existing app.
   The configured member menu includes **Community Feed** (`/news-feed/`) immediately
   after Dashboard, so it remains discoverable when the native header collapses
   overflow items. The Members directory uses **Community Members**
   (`/community-members/`, page 60087), not the existing **No Access** page.
   Native menu icons are configured as Home, Users, Graduation Cap, Star, Trophy,
   Calendar and External Link for Dashboard, Community Feed, My Courses, Skills Vault,
   Challenges, Routines and Next Shift respectively. These are WordPress menu
   settings, not hardcoded icon replacements.
   BuddyPanel defaults to **Open** for new sessions so desktop labels are visible;
   members can still collapse it. **My Courses** retains `/member-dashboard/#courses`.
   The small-community navigation uses one shared feed for questions, training wins
   and updates. Profiles, replies and private messages remain available; no components
   or existing data were disabled/deleted, and separate groups/forums/media directories
   are not promoted in the main member menu.
5. Confirm the saved templates still resolve: dashboard (385), login (1205),
   challenges (3095), routines (32132), My Content (387), support (392),
   homepage (59923, `home.php`), and the membership registration template.
   Their template paths are retained. Account (8) can use the native default page shell.
6. Keep the community's MemberPress access gate; open it under Settings > The Pond
   Community when making the community available to active members.
   This is a membership policy, not a blanket anonymous-site restriction.
7. Save permalinks without changing their structure; purge the existing page/CDN
   caches. Set the site logo/branding in BuddyBoss's theme options as needed.
   Desktop and mobile logos are enabled with the existing full-color
   `THEPOND_RGB_WORDMARK_CLEAN.svg` media item, at 180px and 130px respectively.
   In Performance > Browser Cache > CSS & JS, keep **Remove query strings from
   static resources** disabled. It previously stripped WordPress's asset versions
   while files had a one-year browser-cache lifetime, leaving uploaded changes
   invisible to returning visitors. Only the CSS/JS query-string setting was changed.

### Dashboard refinements (uploaded and verified)

The following three files were uploaded to the existing BuddyBoss Child directory:

- `wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css`
- `wp-content/themes/buddyboss-theme-child-1.0.0/members-templates/member-dashboard.php`
- `wp-content/themes/buddyboss-theme-child-1.0.0/inc/pond.php`

The dashboard-only layout removes outer horizontal padding and the native grid's
negative margins, making the categorized course section exactly as wide as the
available content beside BuddyPanel. Secondary sections retain intentional responsive
gutters. Headings, card grids, section spacing and Skills Vault tabs use consistent
sizes; the template removes old inline spacing and adds a short dashboard introduction.
The skill examples dialog sits above the sticky header and has a responsive width;
its maximum-width override must beat the legacy modal breakpoints' `!important`.
Menu rendering stops marking dashboard anchor links as additional current pages.
Existing carousel, course progress, anchors, skill filtering and dialogs are retained.
No login, enrollment, lesson or community access rules change.

Fresh production navigation without injected preview styles has no document-wide horizontal overflow at
1440, 1920, 768 and 390px. Main-section widths match the content exactly (1195, 1675,
753 and 375px respectively with the new native sidebar state). Headings measure
26px on desktop and 22px on phones. Course carousel scrolling, Passing filtering and
the skill examples dialog were exercised without rating/progress writes.
The portal harness passes 256 checks on PHP 7.4 and 8.5, including menu regression
coverage. The deployed stylesheet SHA-256 matches the local file; its URL retains
the updated version query string. Rendered template output contains the new introduction
and no old inline section padding. Only Dashboard is highlighted on the dashboard;
only Community Feed is highlighted on the feed. The feed returns HTTP 200 without
legacy portal styles, and anonymous homepage/login still return HTTP 200.
Sidebar default and menu-label changes are already saved WordPress settings and do
not require FTP. Regression tests and this documentation do not need uploading.

### Portal appearance and desktop navigation (uploaded and verified)

BuddyBoss Theme and ReadyLaunch use separate appearance settings. The active theme
now has navy `#14345a` saved for its primary/button/selected-sidebar colors.
The child appearance script reads the existing ReadyLaunch light (`#14345a`) and
dark (`#36cce4`) accents without re-enabling ReadyLaunch. On themed frontend pages,
the header sun/moon button switches between light and dark surfaces and chooses
contrasting action text. It shares the native LMS `bbtheme` preference cookie,
saved for one year, and initializes in the head before page rendering. Desktop and
mobile switches and the native lesson dark-mode control share one handler, avoiding
competing toggles. Cookie-save failures are reported explicitly.

Desktop members with BuddyPanel enabled use the sidebar for navigation, not a duplicate
header menu. The header fills its available width and retains account/search/messages/
notifications where the parent theme supplies them; native lesson controls remain.
Mobile menus and logged-out header navigation remain available outside login/signup.
Those auth screens suppress duplicate navigation as described below. If BuddyPanel is disabled,
desktop header navigation remains available. No menu assignments or destinations were removed.

Dark styling covers native community/course pages, the custom dashboard/libraries,
saved content, MemberPress account surfaces, auth forms, portal footer/search and
lesson material. The original standalone marketing homepage remains unchanged.
Consent overlays, embedded video content and external sites retain their own appearance.
Header wordmarks use the configured native white logo in dark mode, without a white
backplate. Category artwork retains its colors on white backplates rather than being inverted.
ReadyLaunch settings remain the accent source; native Theme Colors are the light-mode
fallback when JavaScript is unavailable.

Upload in this order, overwriting the existing paths (the first file is new):

1. `wp-content/themes/buddyboss-theme-child-1.0.0/assets/js/pond-appearance.js`
2. `wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css`
3. `wp-content/themes/buddyboss-theme-child-1.0.0/inc/pond.php`

Do not upload tests or vendor directories. After upload, verify both colors, desktop
sidebar-only navigation, mobile drawer links, keyboard-operable theme buttons and
preference persistence when reloading/navigating between dashboard/community/lessons.
Run `node --test wp-content/plugins/thepond-community/tests/appearance-test.cjs` for
early initialization, shared preference, cookie failure and AA action-text contrast checks.

The uploaded CSS and appearance script matched their local SHA-256 hashes. Fresh
frontend navigation confirmed sidebar-only desktop navigation, both appearance
controls and persistent light/dark preferences after reload, without preview styles.

### MemberPress registration routing and login presentation (uploaded and verified)

Integration 1.2.2 prevents BuddyBoss's native `register` page binding from claiming
MemberPress URLs such as `/register/monthly-membership/`. Previously BuddyBoss
classified these product pages as its own signup component, redirecting anonymous
visitors to login and returning a registration-blocked error to administrators.
The frontend `bp_pages` filter now excludes native registration and activation
bindings from URI matching, without mutating the original map or administrative
page assignments. Activity, profiles and other community routes and membership
gates are unchanged. MemberPress/Firebase remain the signup owners.

The login template replaces the old blank/center/blank columns with a responsive,
light/dark-aware card, keyboard-operable provider buttons, consistent fields and
an aligned password-visibility control. It retains the MemberPress login/reset
shortcode, miniOrange form hook, provider/field identifiers, redirect handling,
page content, optional ACF content and signed-in account actions. The native
registration link and login membership link target the live homepage pricing
section (`/#pricingSect`), not the obsolete subscription-section anchor.

Overwrite these four files at their matching production paths:

1. `wp-content/plugins/thepond-community/thepond-community.php`
2. `wp-content/themes/buddyboss-theme-child-1.0.0/firebase-login.php`
3. `wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css`
4. `wp-content/themes/buddyboss-theme-child-1.0.0/inc/pond.php`

No new plugin activation, theme change or rewrite flush is required. After upload,
verify all three anonymous membership URLs (monthly, yearly and lifetime), the
Firebase provider buttons, email-signup form reveal and preserved checkout fields.
Check login/reset layouts in both appearances at desktop and phone widths, and
confirm anonymous community requests still require login. Do not submit payment,
create subscriptions or send password-reset emails without a designated test.

Local regression coverage: 303 portal/template checks on PHP 7.4 and 8.5, 28
ReadyLaunch community checks on both versions, 29 Firebase checks and 5 appearance
tests. Inert browser previews use the actual rendered PHP template with the live
anonymous MemberPress form: login/reset layouts were checked at 1440px, 390px
and 320px in both appearances, including navy/cyan submit colors, visible labels,
password-toggle alignment and no horizontal overflow. No auth/payment forms were
submitted. The public Join Now menu URL was corrected in WordPress and confirmed
in fresh anonymous frontend output; public desktop/sidebar/mobile assignments
remain unchanged.

After the four-file production upload:

- All three anonymous membership URLs return HTTP 200 with the migrated Member
  Register template, Firebase provider controls and one MemberPress signup form.
  Product IDs remain monthly `479`, yearly `3456` and lifetime `42752`.
- Email/password/confirmation, gateway selection and signup-processing fields
  retain their original identifiers. The selected-membership cookie is set to the
  requested product URL, and the Firebase SDK, miniOrange and custom auth assets load.
- The deployed email-signup UI script shows the form by default and its email
  button reveals it again when hidden; no account or payment was submitted.
- The actual anonymous login/reset responses use the new card. Deployed CSS alone
  passes the same desktop/phone light/dark checks; no preview stylesheet is needed.
  Login posts to `/login/`, password reset retains the existing MemberPress/Firebase
  form, and both membership links reach the real pricing section.
- Anonymous `/news-feed/` and `/community-members/` still redirect to `/login/`.
  `/members/` is not the configured BuddyBoss Members directory.
- The uploaded custom CSS and unchanged appearance script match local SHA-256
  hashes; the fresh header signup link confirms the child helper update.

Actual credential sign-in, OAuth popups, password-reset delivery and paid checkout
were not submitted during these checks. Verify those with an authorized test
account; existing authentication hooks and field contracts are preserved.

### Login/signup refinements (uploaded and verified)

Login and membership signup receive an auth-only `pond-auth-page` body class.
These screens hide the redundant BuddyPanel, desktop menu and mobile drawer/toggle,
remove sidebar offsets, and retain the native header sign-in/sign-up utilities.
Dashboard, community, course and other pages retain their existing navigation.

The existing Forgot Password link moves into the Remember Me row, aligned right
and wrapping cleanly on small phones. If Remember Me is unavailable, the original
link moves before the login submit control. Its destination and behavior are unchanged.

Membership signup uses the same light/dark card and field styling as login, with
desktop paired name/password fields and a single column on phones. Payment choices,
pricing, validation messages and privacy controls use readable theme surfaces;
hidden fields and gateway visibility remain controlled by MemberPress. Provider
buttons retain their miniOrange IDs, and the email button retains its existing
reveal handler. Signed-in checkout still renders the original MemberPress content
without entering the anonymous Firebase signup flow. Configured membership widgets
remain available; absent widgets no longer leave an empty sidebar column.

Native BuddyBoss Theme Options are already saved for desktop/mobile dark logos:
white wordmark media ID `1697`, switches enabled, widths `180`/`130`. The child CSS
switches these images with the shared site-wide appearance preference and removes
the dark logo anchor's white backplate. No additional WordPress configuration,
plugin activation or rewrite flush is needed.

Overwrite these four child-theme files at their matching production paths:

1. `wp-content/themes/buddyboss-theme-child-1.0.0/inc/pond.php`
2. `wp-content/themes/buddyboss-theme-child-1.0.0/assets/js/custom.js`
3. `wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css`
4. `wp-content/themes/buddyboss-theme-child-1.0.0/members-templates/member_register.php`

The integration plugin and login template need no further upload for this refinement.
Do not upload tests or vendor directories. Purge page/CDN caches after upload.

Local validation: 347 portal/template checks and PHP syntax validation on PHP 7.4
and 8.5, 32 Firebase/auth UI checks, and 5 appearance tests pass. Editor diagnostics
are clean. Inert previews combine actual rendered templates, captured production
MemberPress forms and the real UI scripts. Login, signup and reset layouts pass at
1440px, 390px and 320px in both appearances, with no horizontal overflow; signup
keeps its product/payment/privacy fields and correctly sized radios. Forgot Password
stays above submit and right-aligned. Native color/white logos switch correctly.
No credentials, OAuth, password-reset or payment submissions were performed.

After the confirmed four-file production upload:

- Fresh anonymous login/reset and all three membership pages return HTTP 200
  with the auth-only class. Each signup uses the new card, preserves its product
  ID and identity fields, and sets the selected-membership cookie.
- Live custom CSS and custom JS match their local SHA-256 hashes.
- Fresh production HTML and deployed UI assets pass the same 18 light/dark,
  desktop/phone layout checks without injected styles. These inert views strip
  auth/payment scripts and block submissions. Native desktop sign-in/sign-up
  utilities remain visible, duplicate navigation is hidden, the original Forgot
  Password link is right-aligned, and the native white logo has no backplate.
- The deployed signup UI shows email fields by default and its email button
  reveals them again when hidden.
- Authenticated dashboard, News Feed and a sample LearnDash course retain their
  BuddyPanel and do not receive the auth-only class. Anonymous community
  directory/feed requests still redirect to login.
- The browser was restored to the real member dashboard with light appearance.

Actual credential sign-in, OAuth, password-reset delivery and payment completion
remain outside these non-submitting presentation/regression checks.

The Facebook SVG on both auth screens inherits the button's white text color,
overriding the legacy blue icon rule that made it disappear against the blue button.
Browser previews verified white SVG fills on both buttons at 1440px, 390px and
320px in light/dark mode (12 checks); the five appearance regression tests pass.
For this follow-up fix, upload only
`wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css` and purge caches.

### Consistent course progress (uploaded and verified)

Dashboard category cards now call `learndash_course_progress()` with the current
user ID, course ID and `array => true`, matching the native BuddyBoss LearnDash
profile rows, course progress and member-course listings. They no longer calculate
a separate percentage from raw completed steps. LearnDash owns completion status,
step totals, access checks, rounding and zero-step behavior; no progress records,
enrollment, completion actions or course visibility settings are changed.
The card layout, partial-progress labels, completed badge and progress bars remain
unchanged. The dashboard's `[ld_profile]` already uses the native API.

This applies to the active BuddyBoss portal; inactive Melting Pot templates are
left unchanged. No other custom PHP/JavaScript percentage calculation remains
in the active child theme or portable portal.

Upload only `wp-content/plugins/thepond-community/portal/shortcodes.php` for this
progress update, then purge page/CDN caches. No WordPress settings or migration
are needed. The separately committed Facebook icon fix also requires its
`wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css` upload if
not already deployed.

Local validation: 366 portal/template checks and PHP syntax validation pass on
PHP 7.4 and 8.5. Card rendering tests assert current-user/course API arguments,
one API call per course, unchanged visibility, zero/partial/100% bar widths and
labels. Editor diagnostics are clean. After the confirmed upload, all 15 live
dashboard cards match their native profile percentages, including zero, partial
and completed courses. The Skating Level 3 course page also matches its dashboard
card/profile at 12%. No lessons were marked complete or stored progress changed.
The uploaded Facebook stylesheet matches its local SHA-256, and 12 deployed-style
login/signup desktop/phone light/dark icon checks pass without injected CSS.
The browser is restored to the real member dashboard in light mode.

### Matching header logo sizes (uploaded and verified)

Light and dark desktop/mobile wordmarks share 5px vertical padding. Previously
the light image inherited 10px padding while the dark override removed it,
producing different visible sizes despite matching native logo-width settings.
The shared padding places both at the midpoint of their previous visible heights,
without changing SVG assets, header height or native desktop/mobile logo widths.
Upload `wp-content/themes/buddyboss-theme-child-1.0.0/assets/css/custom.css` and
purge page/CDN caches for this refinement.
After the confirmed upload, the live stylesheet matches its local SHA-256.
Both appearances pass desktop (1440px) and phone (390px/320px) checks without
preview CSS: the image content box is consistently 66px high instead of the
previous light/dark 56px/76px, with the 76px header unchanged. The SVG artwork has
matching internal bounds, so the visible wordmarks scale equally. All five
appearance regression tests pass. The browser is restored to desktop light mode.

### Verification and rollback

Before considering the live migration complete, verify desktop/mobile menus,
anonymous homepage and login, an existing member's sign-in/reset/account flow,
dashboard cards/filters/dialogs, native course navigation/progress, lesson video,
audio/downloads, challenge scores, routines, saved content and community pages.
Example existing checks: course 3194 and lesson 3250 (Vimeo video 440111888).
Do not change real course completion, create paid subscriptions or write challenge
scores without a designated test user/action.

Browser-assisted production checks after the 1.2.1 upload:

- BuddyBoss Child and all three integration components are active. Saved menu
  locations were verified; LearnDash 3.0 is selected, Focus Mode is off, the
  profile Courses tab is on, and both social-group/enrollment sync directions are off.
- Anonymous homepage/login and authenticated dashboard, course, lesson, challenges,
  routines, saved content, account, support, resources, profile, feed and Members
  directory return HTTP 200. Sample challenge, routine and individual skill pages
  also render their migrated portal templates.
- Anonymous feed and Members directory requests redirect to the existing login.
  Native community pages do not load legacy dashboard/community styles.
- Dashboard and lesson have no document-wide horizontal overflow at 1440px and
  390px. Native mobile navigation and migrated search open correctly; Firebase has
  one initialized app, and the dashboard retains Skills Vault and footer links.
- After the initial max-width-only stylesheet refinement, the uploaded file's SHA-256 matched the local
  file. Fresh navigation without an injected preview style confirms the dashboard
  fills its available content width: 1297px at a 1440px viewport, 1777px at 1920px,
  and 355px at 390px, with no horizontal overflow. Desktop/mobile logos load, the
  seven meaningful menu icons persist, and the stylesheet URL retains its version.
- The lesson material adapter renders once and retains the Vimeo embed and lesson
  text. The existing cookie-consent tool blocks Vimeo until the visitor selects
  Unblock; actual playback was not verified or consent bypassed.

These live checks used the authenticated administrator session, not a fresh regular
member login or paid registration. The user subsequently confirmed that a fresh
private-window member login successfully opens the dashboard.
Completion, enrollment, challenge-score and
skill-rating writes were not deliberately exercised. Existing cookie/analytics
scripts report `fbq is not defined`; those unrelated scripts were not changed.

Local regression commands (standalone harnesses, no database required):

```sh
npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/portal-test.php
PHP=7.4 npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/portal-test.php
node wp-content/plugins/thepond-community/tests/firebase-test.cjs
```

The portal harness uses real WordPress hooks and the migrated presentation parts;
it checks native-template retention, protected-media access, shortcode output,
asset dependencies, registration-cookie timing and PHP syntax. It is not a full
WordPress/database or production browser test. The existing ReadyLaunch harness
also remains applicable to the Melting Pot rollback path.
Login regression coverage models WordPress's `is_login()` -> `wp_login_url()` ->
`login_url` call chain with a bounded recursion guard, and checks frontend, admin,
core-login, reauthentication and password-reset contexts on both themes.

Rollback: reactivate **Melting Pot Child** and restore its original menu locations
if WordPress remapped them during the switch. The portable portal module stops
loading automatically. The integration and Challenge Score fixes are compatible
with Melting Pot; posts, fields, memberships and progress are not migrated/deleted.

## Previous hybrid BuddyBoss + LearnDash setup (rollback/reference)

Keep **Melting Pot Child** active. The custom [The Pond Community Integration plugin](wp-content/plugins/thepond-community/thepond-community.php)
adds a BuddyBoss community without replacing Firebase/miniOrange authentication,
MemberPress account/billing screens, or LearnDash course templates.

Uploading the files does not activate the integration. Once the custom plugin is
activated, it protects BuddyBoss page, AJAX and REST requests immediately.
Administrators with `manage_options` can preview the community. Other users need
both an open community and any currently active MemberPress membership.
MemberPress's current entitlement check includes valid lifetime/free memberships;
it is not a check for recurring billing or a WordPress role.

The launch switch is **off by default**, under **Settings > The Pond Community**.
Missing BuddyBoss/MemberPress dependencies or the wrong active theme fail closed
and produce an admin notice. There are no custom database tables, group mappings,
roster synchronization, reports or learner-data migrations.

This is a **native BuddyBoss setup with a compatibility layer**, not a custom
community/enrollment system. BuddyBoss owns the feed, profiles, notifications and
staff-managed social groups. BuddyBoss LearnDash owns the optional course/profile
features. WordPress owns menu configuration. The custom code only preserves the
existing theme, authentication/account/avatar flows and MemberPress access policy.

### Included behavior

- ReadyLaunch is controlled by its native activation toggle. It owns community
  layouts and navigation when enabled; Melting Pot Child remains active for
  the rest of the site. The compatibility plugin forces ReadyLaunch's optional
  Login & Registration, Courses and Blog layouts off, even if onboarding tries
  to enable them. These settings do not disable LearnDash or its profile tab.
  Outside community requests, the ReadyLaunch LearnDash helper is not loaded
  and its shared LMS stylesheet is not enqueued, preserving existing course
  template filters and presentation.
- Dedicated [community wrapper](wp-content/themes/meltingpot-child/buddypress.php)
  as the fallback when ReadyLaunch is off, using the member header/footer and scoped
  [styles](wp-content/themes/meltingpot-child/community.css).
  The stylesheet also makes the desktop member navigation container
  content-height on ordinary pages, preventing its inherited percentage height
  from stretching the header. The desktop member logo is capped at the theme's
  existing 60px mobile-logo height. Mobile-menu sizing is unchanged.
  On native BuddyBoss theme-compatibility pages, the integration retains this
  wrapper after Elementor's template filter runs, so Canvas cannot strip the
  member header/footer. Ordinary Elementor pages and embedded views are not
  overridden. Profile panels and community navigation use scoped Pond colors.
- Add Community/Profile/Groups links through **Appearance > Menus**, using the
  existing `member-menu` location. There is no generated navigation, custom
  dashboard feed or dashboard template modification.
- Account & Billing remains separate from the community profile. BuddyBoss
  credential/deletion screens redirect to the existing account screen; its
  corresponding REST mutations are blocked. Notification preferences remain
  available.
- Community login/reset links use the existing login and MemberPress reset
  pages. Login finishes at its existing destination; members then use the menu
  to open the community. No custom return cookie or post-authentication redirect
  handler is installed, and a deep community link is not automatically resumed.
- Existing Firebase-avatar preference and One User Avatar uploads are used in
  community avatars. Avatar changes stay in the existing account avatar tab.
  Group avatars and cover-image permissions are not overridden.
- BuddyBoss registration, account deletion, community user-avatar
  uploads and site-wide private-network mode are suppressed while the custom
  plugin is active. This does not change the existing MemberPress signup flow.
  BuddyBoss's `bp_enable_private_network` flag must return **true** for public
  site access: its redirect implementation restricts the site when false.
  The community's separate MemberPress gate still protects community routes.
- Community Yoast titles and canonical/Open Graph URLs use BuddyBoss's native
  route/title data instead of unrelated WordPress page metadata.
- BuddyBoss AJAX callbacks are identified from their installed source paths,
  including Platform, Platform Pro and BuddyBoss LearnDash. Ineligible users
  cannot receive community heartbeat payloads; normal WordPress/editor
  heartbeat is retained with an explicit community error.
- BuddyBoss LearnDash's native **My Courses Tab** can be enabled independently
  of either group-sync direction. Course/certificate profile features use the
  add-on's own permissions and templates. Existing course pages are untouched.
  Community lives on the mapped Activity/Members/Groups pages; activation does
  not embed feeds or discussions on LearnDash lesson pages.
- **Both group-sync directions stay disabled in native settings.** Social groups
  are managed by staff and do not grant course enrollment. There is no automatic
  course-cohort membership, course-page discussion CTA, custom report policy,
  export-token system, archive mechanism or custom enrollment/report hooks.
  These are configuration choices, not hidden settings forced by custom code;
  changing them later changes native behavior and requires a separate review.

### Exact production FTP upload list

Upload each file to the **same relative path under the production WordPress
root**. Create the `thepond-community` plugin directory if necessary.

| Local file / production destination | Action |
| --- | --- |
| [wp-content/plugins/thepond-community/thepond-community.php](wp-content/plugins/thepond-community/thepond-community.php) | Add |
| [wp-content/themes/meltingpot-child/buddypress.php](wp-content/themes/meltingpot-child/buddypress.php) | Add |
| [wp-content/themes/meltingpot-child/community.css](wp-content/themes/meltingpot-child/community.css) | Add |
| [wp-content/themes/meltingpot-child/firebase-login.php](wp-content/themes/meltingpot-child/firebase-login.php) | Replace after backing up the production copy |
| [wp-content/themes/meltingpot-child/scripts.php](wp-content/themes/meltingpot-child/scripts.php) | Replace after backing up the production copy |

The child theme's assets must be queued on `wp_enqueue_scripts`, not by calling
that action's wrapper while loading the theme. The old early call caused
BuddyBoss HTML templates to precede the `user_email_exists` AJAX JSON response,
making Google/Facebook login incorrectly report that existing accounts were
missing. After uploading `scripts.php`, purge caches and verify that this AJAX
response is JSON only, then retry social login with an existing account.

Do **not** upload the repository's complete `wp-content`, vendor plugin folders,
parent theme, `.gitignore`, README, or test files. The supplied BuddyBoss packages
must already be installed in production, or installed separately through
WordPress using the official distributions. Git ignores those vendor folders
and allows only the custom integration directory as a new exception.

If the production login template differs from the local version, merge the
change rather than overwriting newer production work. Use FTP's temporary-file
upload/rename feature if available so PHP files are not served half-uploaded.
No database or FTP credentials are needed to prepare these files. WordPress
admin access is needed for the activation/settings changes below.

### Production activation order

For the already deployed integration, upload only the updated
`wp-content/plugins/thepond-community/thepond-community.php` for this ReadyLaunch
revision (version 1.1.0). Keep the existing theme files, including the
`scripts.php` login fix. The five-file manifest above is for a fresh installation.
No access-closing or staging-only preview step is required.

1. Back up files/database and retain FTP access. Keep **Melting Pot Child**,
   MemberPress, miniOrange/Firebase, LearnDash, BuddyBoss Platform, BuddyBoss
   LearnDash and The Pond Community Integration active. Keep BuddyBoss Theme,
   the MemberPress BuddyPress add-on and separate BuddyPress/bbPress inactive.
2. Upload the revised integration plugin. If installing from scratch, upload
   all five files and activate the compatibility plugin before BuddyBoss.
3. Open **ReadyLaunch > Activation Settings**, enable ReadyLaunch and complete
   its setup. Leave **Login & Registration**, **Courses** and **Blog** layouts
   off; the compatibility plugin enforces this. Use the Pond logo, light mode
   and navy primary color `#14345A`.
4. In ReadyLaunch's navigation/custom links, add **Dashboard**
   (`/member-dashboard/`), **Account & Billing** (`/account/`) and **Courses**
   (copy the existing Pond Courses menu destination). Community uses
   ReadyLaunch's header/sidebar, not the Pond header. Ordinary pages keep the
   existing Pond layout.
5. Keep the components intentional: Profiles, Activity, Groups, Notifications
   and account notification preferences. Do not automatically enable messaging,
   connections, forums, invitations or media/video/document uploads. In native
   **LearnDash Settings**:
   - Under **Profiles**, enable **My Courses Tab**.
   - Disable **Social Groups to LearnDash Groups** and
     **LearnDash Groups to Social Groups**, including automatic group creation.
   - Keep group reports at their default disabled setting.
   - If previous activation experiments created linked groups or changed report
     settings, review those associations/settings. Disabling sync
     does not erase existing associations or reverse prior enrollment changes.
   No custom enrollment handlers are installed.
   Leave user cover uploads disabled until their privacy/storage behavior has
   been checked.
6. Confirm BuddyBoss directory/page settings use dedicated pages, for example:
   - Activity: `/community/`
   - Members/profiles: `/community-members/`
   - Groups: `/training-groups/`

   Do not use `/members/`: the repository already has a physical member-counter
   directory there. Do not reuse the login, account, checkout or course pages.
   Save WordPress permalinks after mapping the pages. In **Appearance > Menus**,
   add the actual Activity page as **Community** to the existing member menu.
   Use BuddyBoss's native profile/notification menu items where available.
   Keep the existing Account & Billing and course links.
7. Keep BuddyBoss registration/social login disabled. Firebase/miniOrange and
   MemberPress retain the existing login/signup flow. Under **Settings > The Pond
   Community**, leave **Open the community to active members** enabled, or enable
   it if currently off. Membership controls community access; LearnDash controls
   enrollment independently.
8. Exclude community directories and all
    their subpaths, login/account/checkout pages, `admin-ajax.php` and BuddyBoss
    REST routes from page/CDN caching. Exclude authenticated sessions, purge old
    page/CDN caches and confirm bypass headers on real requests.
    PHP's no-cache headers cannot protect content already served by a CDN,
    web-server cache or `advanced-cache.php` before this plugin executes.
9. Verify the release checks below immediately: login/logout, public landing
   page, billing, course/lesson layout and progress, active/inactive member
   access, and ReadyLaunch community pages on desktop/mobile. No waiting period
   is necessary.

### Functional release checks

- **Preview mode:** administrator admitted; active and expired non-admin
  accounts receive a community-not-open response; their existing account and
  course flows remain usable.
- **Open community:** a logged-out deep group/profile link reaches the existing
  login page; email/password, Google and Facebook logins finish at their existing
  destination. The member menu then opens Community. Checkout redirects are
  unchanged; there is no custom deep-link return mechanism.
- **Active membership:** directory, profile, group feed, posting/commenting and
  notification preferences work. AJAX/REST continue to enforce BuddyBoss's own
  nonce, privacy and object-level permissions after the membership gate.
- **Expired/no membership:** community pages and direct BuddyBoss AJAX/REST
  requests fail with explicit errors; Account & Billing stays accessible for
  renewal. Check expiration, refunded access and paid-through cancellations
  against MemberPress's actual entitlement state.
- **Identity:** registration/checkout, Firebase reset/email/password flows and
  logout still work; native BuddyBoss credential changes, account deletion and
  user-avatar writes cannot bypass the existing account flow.
- **Presentation:** selected social/custom avatar is consistent, directory
  visibility is appropriate, existing Community menu links are not duplicated,
  and mobile navigation/forms remain usable. BuddyBoss directories are not
  automatically filtered to show only active members by this release; review
  visibility for staff, inactive accounts and sensitive profile fields.
- **Learning/billing:** course access/completion, subscription updates and
  billing remain unchanged. The native Courses/certificate profile screens use
  the active community layout; check self vs. other-user permissions. Confirm
  no group Course/Reports management tabs or LearnDash association metaboxes
  appear with sync disabled.
- **Independent groups:** joining/leaving/promoting/deleting a social group
  does not affect course enrollment or LearnDash leadership. Adding/removing
  a LearnDash enrollment does not alter social-group membership. Saving a
  LearnDash group does not create a social group. Check both native sync
  switches after activation and again after add-on updates.
- **Caching:** repeat access checks in fresh browsers and after logout/expiry;
  do not rely on an administrator's uncached view.

This gate protects requests executed through WordPress. It does **not** turn
static upload URLs into protected downloads. Keep community media/document
uploads disabled until storage-level privacy and moderation are implemented.
The simplified setup is delivered in this one upload; there is no pending custom
cohort/reporting phase. Private messaging, connections, forums and protected
media storage are not required for the feed/profiles/staff-managed groups.
Enable additional native components only after reviewing their privacy and
moderation requirements. The add-on's own license/dependency checks remain in
charge of its availability; no license checks are bypassed.

### Rollback

To revert only the presentation, disable ReadyLaunch in its native activation
settings and purge caches. The Pond community wrapper returns automatically;
the membership gate remains active. Do not deactivate that gate with BuddyBoss
still active.

To close member access while retaining administrator preview, uncheck the
launch setting. This does not undo posts, groups, settings or database tables.

For a full rollback, **deactivate BuddyBoss Platform and its add-ons first**,
then deactivate The Pond Community Integration. Deactivating the gate while
leaving BuddyBoss active removes its protection. Restore the backed-up login
template, remove only the three newly added production files if desired, and
purge page/CDN caches. Use the database backup if BuddyBoss configuration/data
changes also need to be reverted; uninstalling is not a rollback strategy.

### Local validation

The ReadyLaunch regression harness uses WordPress's real hook implementation
and the installed vendor ReadyLaunch class and LearnDash helper with fixtures.
It checks optional-layout exclusions, native layout selection, fallback,
preserved course-template filters, SEO route data and unchanged access policy.
It does not test real Firebase providers, billing, course completion, CDN
caching or a full WordPress/database boot; run the production checks as well.

```sh
php wp-content/plugins/thepond-community/tests/readylaunch-test.php
php wp-content/plugins/thepond-community/tests/readylaunch-test.php --community
```

If PHP is unavailable locally, the same checks can run in an isolated PHP
WebAssembly CLI without adding repository dependencies:

```sh
npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/readylaunch-test.php
npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/readylaunch-test.php --community
```

Core regression checks and production-file syntax were also validated with
`PHP=7.4` on that CLI; its default runtime used PHP 8.5. No repository dependency
manifest or vendor package was changed.

### Integration references

- [BuddyBoss with other themes](https://buddyboss.com/docs/can-you-use-other-theme-with-buddyboss-platform/)
- [ReadyLaunch template and integration restrictions](https://buddyboss.com/blog/introducing-readylaunch/)
- [MemberPress BuddyBoss account takeover and registration behavior](https://memberpress.com/docs/buddyboss-integration/)
- [LearnDash-to-social-group synchronization](https://buddyboss.com/docs/sync-learndash-groups-with-buddyboss-social-groups/)
