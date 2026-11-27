#!/usr/bin/env bash
# Updates the "production" branch: main + the compiled front-end files
# (public/build), so the server can deploy with "git pull origin production"
# without Node.js. The branch only ever moves forward (merge + commit, never a
# force-push), so a server can always fast-forward to it.
#
# Expects: a full clone (fetch-depth 0) with main checked out, Node.js and
# write access to origin. Usage: update-production-branch.sh [branch]
set -euo pipefail

BRANCH="${1:-production}"
SOURCE_SHA="$(git rev-parse HEAD)"
SOURCE_SHORT="$(git rev-parse --short HEAD)"

git config user.name "github-actions[bot]"
git config user.email "41898282+github-actions[bot]@users.noreply.github.com"

if git ls-remote --exit-code --heads origin "$BRANCH" >/dev/null 2>&1; then
    git fetch --quiet origin "$BRANCH"
    git checkout --quiet -B "$BRANCH" "origin/$BRANCH"
    # Bring in the new code. Only public/build differs from main, and main
    # never contains it, so this merge cannot conflict.
    git merge --quiet --no-edit --no-ff -m "Merge main (${SOURCE_SHORT}) into ${BRANCH}" "$SOURCE_SHA"
else
    git checkout --quiet -B "$BRANCH" "$SOURCE_SHA"
fi

# Build from exactly what is now on the branch.
npm ci --no-audit --no-fund
npm run build
test -f public/build/manifest.json

# public/build is in .gitignore on main: add it explicitly here, replacing
# the previous build so old hashed files do not pile up.
git rm -r --cached --quiet --ignore-unmatch public/build
git add --force public/build

if git diff --cached --quiet; then
    echo "Front-end build unchanged."
else
    git commit --quiet -m "Build front-end assets for ${SOURCE_SHORT}"
fi

git push --quiet origin "$BRANCH"
echo "${BRANCH} is now at $(git rev-parse --short HEAD) (code from main ${SOURCE_SHORT})."
