#!/bin/bash
rm -rf .onion/

# bundle with new dependencies
onion bundle || exit

# compile to phar file
scripts/compile.sh || exit

# build new package.xml
onion -d build || exit

# use pear to install 
pear install -a -f package.xml || exit

# git commit -a -m 'Make new release'
# git push origin HEAD
