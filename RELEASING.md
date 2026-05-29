# Releasing & Updating — Enamel Reviews Feed

This plugin **self-updates from its GitHub releases** — the same mechanism the
**Enamel Store Locator** and **Enamel Insurance Form** plugins already use. There
is **no helper plugin** (no Git Updater). WordPress shows the update right in
Dashboard → Updates / Plugins, and **Update Now** pulls the release zip.

How it works: `erf_check_for_update()` (in `enamel-reviews-feed.php`) hooks
`pre_set_site_transient_update_plugins`, calls the GitHub "latest release" API
for `drharcho/enamel-reviews-feed`, and if the release tag is newer than the
`ERF_VERSION` constant, hands WordPress the release's zip asset.

---

## One-time bootstrap (only because the live site predates the self-updater)

The version currently on the site (v1.1.0) has no self-updater code, so it can't
detect updates yet. Upload **one** version that contains the updater, once:

1. WP Admin → Plugins → Add New → Upload Plugin.
2. Choose the latest `enamel-reviews-feed.zip` (from the GitHub release, or the
   local build at `/Users/harcho/projects/enamel-reviews-feed.zip`).
3. Install → when prompted, **Replace current with uploaded**.

From then on, every future release is detected automatically — no more uploads.

---

## Shipping an update (every time after bootstrap)

1. **Edit** code locally. Test against `preview.html` if it's a widget/CSS change.

2. **Bump the version in TWO places** to the same new number:
   - The `Version:` header in `enamel-reviews-feed.php`
   - The `ERF_VERSION` constant just below it

   > If they disagree, a red admin warning appears (the `erf_version_guard`).
   > The GitHub tag must also match, e.g. header `1.1.3` ↔ tag `v1.1.3`.

3. **Commit, push, tag, and cut a release with the zip:**
   ```bash
   cd /Users/harcho/projects/enamel-reviews-feed
   git commit -am "v1.1.3: <what changed>"
   git push
   git tag v1.1.3 && git push --tags

   # rebuild the zip (folder named enamel-reviews-feed/ inside)
   cd /Users/harcho/projects
   rm -f enamel-reviews-feed.zip
   zip -rq enamel-reviews-feed.zip enamel-reviews-feed/ -x "*/.claude/*" "*/preview.html" "*/.git/*"

   gh release create v1.1.3 enamel-reviews-feed.zip \
     --repo drharcho/enamel-reviews-feed --title "v1.1.3" --notes "<what changed>"
   ```

4. **Update on the site:** WP Admin → Dashboard → Updates → **Check Again**
   (or just wait — WP checks roughly every 12 h). The plugin shows
   **update available** → click **Update Now**.

That's it. No ZIP upload, no SFTP, no SSH after the one-time bootstrap.

> **Why attach the zip to the release?** The self-updater prefers the uploaded
> `.zip` asset because its internal folder is `enamel-reviews-feed/` (what
> WordPress needs). If no asset is attached it falls back to GitHub's auto
> zipball, and the `erf_fix_update_folder()` filter renames the extracted
> folder. Attaching the zip is the clean path — always do step 3's `gh release`.

---

## Versioning convention (semver-lite)

| Change | Bump | Example |
|---|---|---|
| Copy default, tiny CSS tweak, typo | patch | 1.1.2 → 1.1.**3** |
| New feature (e.g., a new admin field) | minor | 1.1.3 → 1.**2**.0 |
| Breaking change (e.g., feed JSON shape) | major | 1.2.0 → **2**.0.0 |

---

## What does NOT need a release at all

Live-editable in WP Admin → Settings → Enamel Reviews — change and save, no
version bump, no deploy:

- Headlines, ledes, button text (per location + generic)
- Booking URLs and Google listing URLs
- The Google API key
- **The 10 Google Place IDs**
- Review display filters (min rating, min length, mini-card count)
- Which page shows which location (the `data-location` attribute in Elementor)

Only ship a release when you change **code** — PHP logic, the widget HTML
structure, or the CSS/JS in `assets/`.

---

## Rolling back

Re-cut the previous version as a higher tag, or locally:

```bash
git revert <bad-commit> && git push
# bump version + tag as a new patch release, then Update Now on the site
```
