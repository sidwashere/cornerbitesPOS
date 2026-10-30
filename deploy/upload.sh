#!/usr/bin/env bash
#
# upload.sh - Upload the Cornerbites deployment packages to cPanel over FTP.
#
# It uploads the two zip files built into ../dist/ to the cPanel home directory.
# After uploading, extract them from cPanel > File Manager (server-side, fast):
#   - cornerbites-website.zip -> /cornerbites.co.ke/
#   - cornerbites-pos.zip     -> /pos.cornerbites.co.ke/
#
# Credentials are read from deploy/.ftpcreds (NEVER commit that file).
#   cp deploy/.ftpcreds.example deploy/.ftpcreds   # then edit
#
# Usage:
#   ./deploy/upload.sh            # upload both zips
#   ./deploy/upload.sh --dry-run  # show what would run, do nothing
#
set -euo pipefail
cd "$(dirname "$0")"

DIST="../dist"
CREDS=".ftpcreds"
DRY_RUN=0
[ "${1:-}" = "--dry-run" ] && DRY_RUN=1

say() { printf '%s\n' "$*"; }

if [ ! -f "$CREDS" ]; then
  say "ERROR: $CREDS not found."
  say "Run:  cp deploy/.ftpcreds.example deploy/.ftpcreds   then edit it."
  exit 1
fi
# shellcheck disable=SC1090
source "$CREDS"

: "${FTP_HOST:?Set FTP_HOST in $CREDS}"
: "${FTP_USER:?Set FTP_USER in $CREDS}"
: "${FTP_PASS:?Set FTP_PASS in $CREDS}"
FTP_PORT="${FTP_PORT:-21}"
# Remote directory to drop the zips (cPanel home). Adjust if needed.
REMOTE_DIR="${REMOTE_DIR:-.}"

WEB_ZIP="$DIST/cornerbites-website.zip"
POS_ZIP="$DIST/cornerbites-pos.zip"
for f in "$WEB_ZIP" "$POS_ZIP"; do
  [ -f "$f" ] || { say "ERROR: missing $f - run the build first."; exit 1; }
done

say "Host: $FTP_HOST:$FTP_PORT  User: $FTP_USER  Remote dir: $REMOTE_DIR"
say "Uploading:"
say "  $(basename "$WEB_ZIP")  ($(du -h "$WEB_ZIP" | cut -f1))"
say "  $(basename "$POS_ZIP")  ($(du -h "$POS_ZIP" | cut -f1))"

if [ "$DRY_RUN" = "1" ]; then
  say "--dry-run: nothing uploaded."
  exit 0
fi

lftp -u "$FTP_USER","$FTP_PASS" -p "$FTP_PORT" "ftp://$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:ssl-allow true
set ftp:passive-mode true
set net:max-retries 3
set net:timeout 30
set mirror:use-pget-n 4
cd "$REMOTE_DIR"
put "$WEB_ZIP"
put "$POS_ZIP"
ls -l
bye
EOF

say ""
say "Done. Now in cPanel > File Manager:"
say "  1. Go to the home directory (where the zips landed)."
say "  2. Extract cornerbites-website.zip into  /cornerbites.co.ke/"
say "  3. Extract cornerbites-pos.zip      into  /pos.cornerbites.co.ke/"
say "  4. Delete the zips after extracting."
