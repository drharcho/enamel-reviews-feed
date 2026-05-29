# Releasing & Updating — Enamel Reviews Feed

This plugin updates itself from GitHub. Once **Git Updater** is installed on the
site, you never upload a ZIP again — pushed releases appear as a one-click
**Update Now** button in WP Admin, exactly like the credentialing app.

---

## One-time setup (do this once, ever)

### 1. Put the plugin on GitHub

From the plugin folder on your machine:

```bash
cd /Users/harcho/projects/enamel-reviews-feed
git init
git add .
git commit -m "Initial commit: Enamel Reviews Feed v1.0.0"
git branch -M main
git remote add origin https://github.com/drharcho/enamel-reviews-feed.git
git push -u origin main
git tag v1.0.0
git push --tags
```

> If you use a different GitHub repo name or account, update the
> `Update URI` and `GitHub Plugin URI` headers at the top of
> `enamel-reviews-feed.php` to match.

### 2. Install Git Updater on the WordPress site

1. Download the latest release ZIP from <https://github.com/afragen/git-updater/releases>
   (file named `git-updater-x.x.x.zip`).
2. WP Admin → Plugins → Add New → Upload Plugin → choose that ZIP → Activate.
   *(This is the last ZIP upload you'll ever do.)*

### 3. Connect Git Updater to your repo

- **If the repo is PUBLIC:** nothing else needed — Git Updater finds it from the
  headers automatically.
- **If the repo is PRIVATE:** create a GitHub Personal Access Token
  (github.com → Settings → Developer settings → Fine-grained token, read-only
  on the `enamel-reviews-feed` repo), then paste it under
  **Settings → Git Updater → GitHub** → "GitHub Access Token".

### 4. Confirm

WP Admin → Settings → Git Updater → "Installed" tab. You should see
**Enamel Reviews Feed** listed with its current version and branch `main`.

Done. From now on, shipping an update is the loop below.

---

## Shipping an update (every time)

1. **Edit** code locally. Test against `preview.html` if it's a widget/CSS change.

2. **Bump the version in TWO places** to the same new number:
   - The `Version:` header in `enamel-reviews-feed.php`
   - The `ERF_VERSION` constant just below it

   > If these two ever disagree, the plugin shows a red admin warning and the
   > update may silently fail. The guard exists specifically to catch this.

3. **Commit, push, tag:**
   ```bash
   git add .
   git commit -m "v1.0.1: <what changed>"
   git push
   git tag v1.0.1
   git push --tags
   ```
   The tag (`v1.0.1`) must match the version number (`1.0.1`). Git Updater
   compares the tag to the header.

4. **Update on the site:** WP Admin → Dashboard → Updates (or the Plugins page)
   shows **Enamel Reviews Feed — update available**. Click **Update Now**.

That's it. No ZIP, no SFTP, no SSH.

---

## Versioning convention (semver-lite)

| Change | Bump | Example |
|---|---|---|
| Copy default, tiny CSS tweak, typo | patch | 1.0.0 → 1.0.**1** |
| New feature (e.g., a new admin field) | minor | 1.0.1 → 1.**1**.0 |
| Breaking change (e.g., feed JSON shape) | major | 1.1.0 → **2**.0.0 |

---

## What does NOT need a release at all

These are live-editable in WP Admin → Settings → Enamel Reviews — change and
save, no version bump, no deploy:

- Headlines, ledes, button text (per location + generic)
- Booking URLs and Google listing URLs
- The Google API key
- Which page shows which location (the `data-location` attribute in Elementor)

Only ship a release when you change **code** — PHP logic, the widget HTML
structure, or the CSS/JS in `assets/`.

---

## Rolling back

If a release breaks something, in Git Updater → "Installed" tab each plugin has
a **branch/tag switcher** — pick the previous tag (e.g. `v1.0.0`) and it
reinstalls that version. Or locally:

```bash
git revert <bad-commit> && git push
# then bump version + tag as a new patch release
```
