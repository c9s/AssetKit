#!/bin/bash
VERSION=$(cat package.ini | grep "^version" | perl -pe 's/version\s*=\s*//')
DATE=$(date)
git tag $VERSION -f -m "Release $VERSION at $DATE"
git push origin --tags
