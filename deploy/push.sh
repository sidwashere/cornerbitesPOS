#!/usr/bin/env bash
#
# push.sh - Fast, precise FTP deploys to the live Cornerbites sites, separately.
#
# DISCOVERED 2026-11-02 (critical): this hosting account has TWO parallel trees
# and the live web server reads them asymmetrically:
#
#   pos.cornerbites.co.ke  ->  /home/mllbontz/home/mllbontz/pos.cornerbites.co.ke
#                              (a NESTED legacy path - this is the REAL doc root)
#   cornerbites.co.ke      ->  /home/mllbontz/cornerbites.co.ke
#                              (top-level - normal)
#
# The cPanel "Domains" page claims the POS doc root is /home/mllbontz/pos.cornerbites.co.ke -
# THAT IS A STALE, UNSERVED COPY. Writing there (cPanel File Manager, UAPI, or FTP accounts
# scoped there) has NO effect on the live site. Always deploy with this script.
#
# Usage:
#   ./deploy/push.sh pos               # push committed POS changes since the last push
#   ./deploy/push.sh pos <commit>      # push committed POS changes since <commit>
#   ./deploy/push.sh pos --all         # FULL mirror resync (slower, thorough)
#   ./deploy/push.sh web [...]         # same, for cornerbites.co.ke
#   ./deploy/push.sh all               # pos + web (since-last mode)
#   append --dry-run to any command    # preview only, upload nothing
#
# How the default (since-last) mode works:
#   1. Base commit = deploy/.lastdeploy-<target> marker (or the <commit> you pass)
#   2. Files = git diff --name-only --diff-filter=d <base>..HEAD (tracked files only,
#      filtered per target), so local junk, zips and secrets are never pushed
#   3. Each file is uploaded over FTP into the live doc root (one lftp session)
#   4. Every upload is size-verified; only then is the marker updated to HEAD
#
# Safety:
#   - library/wpos/.dbconfig.json and docs/ are gitignored, so they can never be pushed
#   - node_modules/, dist/, deploy/, website/ (for pos) are excluded
#   - Nothing is ever deleted on the server
#   - --all does a full lftp mirror (uploads changed files, never deletes)
#
set -euo pipefail
cd "$(dirname "$0")/.."   # repo root
REPO_ROOT="$(pwd)"

CREDS="deploy/.ftpcreds"
if [ ! -f "$CREDS" ]; then
  echo "ERROR: $CREDS not found."
  echo "Run:  cp deploy/.ftpcreds.example deploy/.ftpcreds   # then edit"
  exit 1
fi
# shellcheck disable=SC1090
source "$CREDS"

: "${FTP_HOST:?Set FTP_HOST in $CREDS}"
FTP_PORT="${FTP_PORT:-21}"
: "${POS_USER:?Set POS_USER in $CREDS}"
: "${POS_PASS:?Set POS_PASS in $CREDS}"
: "${WEB_USER:?Set WEB_USER in $CREDS}"
: "${WEB_PASS:?Set WEB_PASS in $CREDS}"

DRY=0
ARGS=()
for a in "$@"; do
  if [ "$a" = "--dry-run" ]; then DRY=1; else ARGS+=("$a"); fi
done
TARGET="${ARGS[0]:-}"
BASE="${ARGS[1]:-}"

say() { printf '%s\n' "$*"; }

if [ -z "$TARGET" ]; then
  say "Usage: $0 {pos|web|all} [<commit>|--all] [--dry-run]"
  exit 1
fi

MARKER="deploy/.lastdeploy-"

# ---- helpers -------------------------------------------------------------

lftp_run() { # $1=user $2=pass ; lftp commands on stdin
  lftp -u "$1","$2" -p "$FTP_PORT" "ftp://$FTP_HOST"
}

remote_path() { # $1 = repo-relative file -> path inside the FTP account root
  if [ "$TARGET" = "web" ]; then echo "/${1#website/}"; else echo "/$1"; fi
}

collect_files() { # $1 = base commit -> FILES[]
  local base="$1"
  FILES=()
  local pathspec
  if [ "$TARGET" = "web" ]; then
    pathspec=('website')
  else
    pathspec=('.' ':(exclude)website' ':(exclude)deploy' ':(exclude)dist'
              ':(exclude)node_modules' ':(exclude)wallacepos-master.zip' ':(exclude)docs')
  fi
  while IFS= read -r f; do
    [ -n "$f" ] && FILES+=("$f")
  done < <(git diff --name-only --diff-filter=d "$base"..HEAD -- "${pathspec[@]}")
}

push_changes() { # $1=user $2=pass $3=base
  local user="$1" pass="$2" base="$3"
  collect_files "$base"
  if [ "${#FILES[@]}" -eq 0 ]; then
    say "Nothing to push for $TARGET (already at $base..HEAD = no tracked changes)."
    return 0
  fi
  say "Pushing ${#FILES[@]} file(s) to $TARGET:"
  local f
  for f in "${FILES[@]}"; do say "  $(remote_path "$f")"; done
  if [ "$DRY" = 1 ]; then say "(dry-run: nothing uploaded)"; return 0; fi

  {
    echo "set ssl:verify-certificate no"
    echo "set ftp:ssl-allow true"
    echo "set ftp:passive-mode true"
    echo "set net:max-retries 3"
    echo "set net:timeout 30"
    for f in "${FILES[@]}"; do
      rdir="$(dirname "$(remote_path "$f")")"
      echo "mkdir -p \"$rdir\""
      echo "put -O \"$rdir\" \"$REPO_ROOT/$f\""
    done
    echo "bye"
  } | lftp_run "$user" "$pass"

  # size verification (batched in one session)
  say "Verifying uploads..."
  local expected actual bad=0 i=0
  expected=""
  for f in "${FILES[@]}"; do
    expected+="$f $(wc -c < "$REPO_ROOT/$f" | tr -d ' ')"$'\n'
  done
  actual=$({
    echo "set ssl:verify-certificate no"
    echo "set ftp:ssl-allow true"
    for f in "${FILES[@]}"; do echo "ls -la \"$(remote_path "$f")\""; done
    echo "bye"
  } | lftp_run "$user" "$pass" 2>/dev/null | awk 'NF>=9 {print $9, $5} NF==9 {print $9, $5}')
  # ls line: perms links owner group size ... path  -> $5=size, $9=path
  while IFS= read -r line; do
    [ -z "$line" ] && continue
    f="${line%% *}"
    want="${line##* }"
    got=$(printf '%s\n' "$actual" | awk -v p="$(remote_path "$f")" '$1==p {print $2; exit}')
    if [ "$got" = "$want" ]; then
      say "  OK  $(remote_path "$f") ($want bytes)"
    else
      say "  FAIL $(remote_path "$f") (local $want, remote ${got:-MISSING})"
      bad=1
    fi
  done <<< "$expected"
  if [ "$bad" = 1 ]; then
    say "VERIFICATION FAILED - deploy marker NOT updated. Re-run after fixing."
    exit 1
  fi
  git rev-parse HEAD > "${MARKER}${TARGET}"
  say "Marker updated: ${MARKER}${TARGET} -> $(git rev-parse --short HEAD)"
}

push_mirror() { # $1=user $2=pass  (full resync)
  local user="$1" pass="$2"
  say "FULL mirror -> $TARGET (uploads changed files, never deletes)"
  if [ "$TARGET" = "web" ]; then
    lftp -u "$user","$pass" -p "$FTP_PORT" "ftp://$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:ssl-allow true
set ftp:passive-mode true
set net:max-retries 3
set net:timeout 30
mirror --reverse --verbose $( [ "$DRY" = 1 ] && echo --dry-run ) \
  -x '\.DS_Store' -x '\.ftpquota' \
  "$REPO_ROOT/website" /
bye
EOF
  else
    lftp -u "$user","$pass" -p "$FTP_PORT" "ftp://$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:ssl-allow true
set ftp:passive-mode true
set net:max-retries 3
set net:timeout 30
mirror --reverse --verbose $( [ "$DRY" = 1 ] && echo --dry-run ) \
  -x '\.git' -x '^dist/' -x '^deploy/' -x '^website/' -x '\.opencode' \
  -x '^docs/' -x '^node_modules/' -x '\.dbconfig\.json' \
  -x '\.mybroken' -x '\.DS_Store' -x '\.ftpquota' -x '\.trash' \
  -x 'wallacepos-master\.zip' \
  "$REPO_ROOT" /
bye
EOF
  fi
  if [ "$DRY" = 0 ]; then
    git rev-parse HEAD > "${MARKER}${TARGET}"
    say "Marker updated: ${MARKER}${TARGET} -> $(git rev-parse --short HEAD)"
  fi
}

resolve_base() { # $1=marker path -> echoes base commit
  local m="$1"
  if [ -f "$m" ]; then cat "$m"; else echo ""; fi
}

# ---- dispatch ------------------------------------------------------------

run_target() { # $1=target(pos|web)
  TARGET="$1"
  local user pass m base
  if [ "$TARGET" = "web" ]; then user="$WEB_USER"; pass="$WEB_PASS"; else user="$POS_USER"; pass="$POS_PASS"; fi
  m="${MARKER}${TARGET}"
  if [ "$BASE" = "--all" ]; then
    push_mirror "$user" "$pass"
    return
  fi
  base="${BASE:-$(resolve_base "$m")}"
  if [ -z "$base" ]; then
    say "No deploy marker for '$TARGET' (${m})."
    say "Provide a base commit:  ./deploy/push.sh $TARGET <commit>"
    say "Or do a full resync:    ./deploy/push.sh $TARGET --all"
    exit 1
  fi
  say "Base: $(git rev-parse --short "$base")  HEAD: $(git rev-parse --short HEAD)"
  push_changes "$user" "$pass" "$base"
}

case "$TARGET" in
  pos) run_target pos ;;
  web) run_target web ;;
  all) run_target pos; say ""; run_target web ;;
  *) say "Unknown target: $TARGET (use pos|web|all)"; exit 1 ;;
esac

say ""
say "Done. Verify:"
say "  POS:      https://pos.cornerbites.co.ke/admin/   (hard-refresh)"
say "  Website:  https://cornerbites.co.ke/             (hard-refresh)"
say "Tip: bump the ?v= cache-buster in admin/index.html whenever admin/assets/wpos/wpos.js changes."
