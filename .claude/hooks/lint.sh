#!/usr/bin/env bash
# PostToolUse hook: lint PHP and YAML files Claude edits under src/.
# Exit code 2 sends the error back to Claude so it can fix the file.
input=$(cat)
file=$(echo "$input" | jq -r '.tool_input.file_path // empty')

[ -z "$file" ] && exit 0
case "$file" in */vendor/*) exit 0 ;; esac
[ -f "$file" ] || exit 0

case "$file" in
  *.php)
    out=$(php -l "$file" 2>&1) || { echo "$out" >&2; exit 2; }
    ;;
  *.yml|*.yaml)
    out=$(symfony console lint:yaml "$file" 2>&1) || { echo "$out" >&2; exit 2; }
    ;;
esac
exit 0
