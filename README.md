# The Pond
A wordpress site that uses learndash/memberpress to provide the most efficient hockey skills courses in the world.

## BuddyBoss + LearnDash integration

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

- Dedicated [community wrapper](wp-content/themes/meltingpot-child/buddypress.php)
  using the existing member header/footer and narrowly scoped
  [styles](wp-content/themes/meltingpot-child/community.css).
  The stylesheet also makes the desktop member navigation container
  content-height on ordinary pages, preventing its inherited percentage height
  from stretching the header. The desktop member logo is capped at the theme's
  existing 60px mobile-logo height. Mobile-menu sizing is unchanged.
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
- ReadyLaunch, BuddyBoss registration, account deletion, community user-avatar
  uploads and site-wide private-network mode are suppressed while the custom
  plugin is active. This does not change the existing MemberPress signup flow.
  BuddyBoss's `bp_enable_private_network` flag must return **true** for public
  site access: its redirect implementation restricts the site when false.
  The community's separate MemberPress gate still protects community routes.
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
  export-token system, archive mechanism or vendor-hook replacement.
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

1. Take a restorable **database and file backup**, including the existing theme
   templates and plugin/theme settings. BuddyBoss activation itself may create
   database tables/settings; the custom plugin does not replace that process.
   Keep FTP access available for recovery.
2. Confirm the existing email/password, Google/Facebook login, signup/checkout,
   password reset, account email/password changes, subscription management,
   avatar selection, logout and LearnDash progress flows work before deployment.
3. Confirm PHP >= 7.4 and WordPress >= 6.6, **Melting Pot Child** is active,
   MemberPress/miniOrange remain active, and the membership/login/account pages
   still use their existing templates. Do not activate BuddyBoss Theme,
   ReadyLaunch, the MemberPress BuddyPress add-on or separate BuddyPress/bbPress
   alongside this setup.
4. Upload all **four** files listed above.
   Activate **The Pond Community
   Integration first** in Plugins. Leave its launch checkbox unchecked.
   A warning that BuddyBoss is not active yet is expected.
5. Activate **BuddyBoss Platform** and **BuddyBoss LearnDash** in this same
   deployment, with **LearnDash LMS** already active and the add-on license valid.
   Keep BuddyBoss Theme inactive. Use the standard **Nouveau** template pack.
   Optional Platform Pro features are not required for this integration.
6. Enable the initial components: Member Profiles/Profile Fields, Activity
   Feeds, Social Groups and Notifications, plus account settings if needed for
   notification preferences. Leave messaging, connections, forums, invitations,
   media/video/document uploads disabled. In native **LearnDash Settings**:
   - Under **Profiles**, enable **My Courses Tab**.
   - Disable **Social Groups to LearnDash Groups** and
     **LearnDash Groups to Social Groups**, including automatic group creation.
   - Keep group reports at their default disabled setting.
   - If previous activation experiments created linked groups or changed report
     settings, review/unlink/reset those before opening access. Disabling sync
     does not erase existing associations or reverse prior enrollment changes.
   No custom LearnDash settings filters or enrollment handlers are installed.
   Leave user cover uploads disabled until their privacy/storage behavior has
   been checked.
7. In BuddyBoss directory/page settings, use dedicated pages with these slugs:
   - Activity: `/community/`
   - Members/profiles: `/community-members/`
   - Groups: `/training-groups/`

   Do not use `/members/`: the repository already has a physical member-counter
   directory there. Do not reuse the login, account, checkout or course pages.
   Save WordPress permalinks after mapping the pages. In **Appearance > Menus**,
   add the actual Activity page as **Community** to the existing member menu.
   Use BuddyBoss's native profile/notification menu items where available.
   Keep the existing Account & Billing and course links.
8. Keep BuddyBoss registration/social login and ReadyLaunch disabled in the
   admin configuration as well. The MemberPress BuddyPress/BuddyBoss add-on
   remains inactive because its default integration takes over the account
   experience. An active MemberPress membership opens community access;
   LearnDash continues to control course enrollment independently.
9. Create a few **private, staff-managed** groups: Introductions, Weekly
   Challenge & Progress, and Ask a Coach. Restrict creation/invitations to staff
   and configure group visibility/joins deliberately. Joining a group must not
   grant a course enrollment. Seed a welcome post and moderation guidelines.
   Manage their rosters with BuddyBoss's native group tools, not LearnDash sync.
   Staff can create training-topic groups, but their membership will not
   automatically reflect enrollment changes. Do not share enrollment-restricted
   course materials in a general social group.
10. **Before previewing or opening**, exclude community directories and all
    their subpaths, login/account/checkout pages, `admin-ajax.php` and BuddyBoss
    REST routes from page/CDN caching. Exclude authenticated sessions, purge old
    page/CDN caches and confirm bypass headers on real requests.
    PHP's no-cache headers cannot protect content already served by a CDN,
    web-server cache or `advanced-cache.php` before this plugin executes.
11. Preview as an administrator on desktop/mobile. Confirm the existing member
    header/footer, avatar, community tabs and forms render correctly, and the
    ordinary dashboard/account/course screens have not changed unexpectedly.
12. In a controlled low-traffic window, check **Open the community to active
    members** and run the release checks below immediately using designated
    test accounts. This opens access to all eligible members, not just a pilot
    subset. Close the checkbox again if any check fails.

### Release checks before leaving access open

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
  the existing member shell; check self vs. other-user permissions. Confirm
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

To close member access while retaining administrator preview, uncheck the
launch setting. This does not undo posts, groups, settings or database tables.

For a full rollback, **deactivate BuddyBoss Platform and its add-ons first**,
then deactivate The Pond Community Integration. Deactivating the gate while
leaving BuddyBoss active removes its protection. Restore the backed-up login
template, remove only the three newly added production files if desired, and
purge page/CDN caches. Use the database backup if BuddyBoss configuration/data
changes also need to be reverted; uninstalling is not a rollback strategy.

### Local validation

The standalone regression harness uses WordPress's real hook, error and REST
request classes, with fixtures for users, entitlements and URLs. It tests policy
and wiring without a database, not the real providers, billing lifecycle,
rendered layout, web-server caching or a full WordPress boot.
The native LearnDash harness loads the supplied add-on's actual **Settings and
Sync** classes with the documented configuration. It verifies that profile
courses can be enabled with both synchronization directions disabled and that
native social/enrollment events do no association or enrollment work. It does
not apply those settings to production or replace the vendor's hooks.

```sh
php wp-content/plugins/thepond-community/tests/community-test.php
php wp-content/plugins/thepond-community/tests/community-test.php --without-memberpress
php wp-content/plugins/thepond-community/tests/community-test.php --without-buddyboss
php wp-content/plugins/thepond-community/tests/native-learndash-test.php
```

If PHP is unavailable locally, the same checks can run in an isolated PHP
WebAssembly CLI without adding repository dependencies:

```sh
npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/community-test.php
npm exec --yes --package=@php-wasm/cli@3.1.57 -- php-wasm-cli wp-content/plugins/thepond-community/tests/native-learndash-test.php
```

Core regression checks and production-file syntax were also validated with
`PHP=7.4` on that CLI; its default runtime used PHP 8.5. No repository dependency
manifest or vendor package was changed.

### Integration references

- [BuddyBoss with other themes](https://buddyboss.com/docs/can-you-use-other-theme-with-buddyboss-platform/)
- [ReadyLaunch template and integration restrictions](https://buddyboss.com/blog/introducing-readylaunch/)
- [MemberPress BuddyBoss account takeover and registration behavior](https://memberpress.com/docs/buddyboss-integration/)
- [LearnDash-to-social-group synchronization](https://buddyboss.com/docs/sync-learndash-groups-with-buddyboss-social-groups/)
