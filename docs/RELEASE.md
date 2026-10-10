# Release Notes

## 1.0.1 (2026-10-10)

No change to the plugin's behaviour.

### Changes

- The manifest declares an update server, so Joomla's updater offers new versions from the `joomla_update_system` repository. Sites on 1.0.0 need to install 1.0.1 by hand once; later versions arrive through **System → Update → Extensions**.
- The release package is reproducible: it is built from the git tag with a deterministic packager, so anyone can rebuild it and get the checksum the update descriptor publishes.

## 1.0.0 (2026-02-19)

Initial release of `plg_system_cbgjguestaccess`.

### Features

- Redirects guest visitors to the CB login page when accessing any CB GroupJive URL except events.
- Allowlist model: only `cbgroupjiveevents` is permitted for guests. All other `cbgroupjive*` plugin elements are blocked. New sub-plugins are blocked by default.
- Scoped to GroupJive only: non-GroupJive CB pluginclass pages (`cbactivity`, `cbprivacy`, etc.) are not affected.
- Preserves the original URL as a base64-encoded `return` parameter so guests are redirected back after login.
- Shows a translatable "please log in" notice message on the login page.

### Requirements

- Joomla 5.x
- PHP 8.1+
- Community Builder 2.x with CB GroupJive
- `plg_system_cbgjvisibility` (companion plugin, deployed separately)

### Post-install configuration

- Set plugin ordering above "System - Community Builder".
- Verify CB GroupJive Events "Events Access" is set to Public (level 1).
- Verify `plg_system_cbgjvisibility` is enabled.
- Set GroupJive tab access to "Registered" in CB Tab Manager.
