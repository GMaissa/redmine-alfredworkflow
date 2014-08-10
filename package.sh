#!/bin/sh -x

rm Redmine.alfredworkflow
composer update --no-dev
zip -r Redmine . -x config/settings.json -x package.sh \*phpunit.xml.dist \*composer.json \*composer.lock \*.gitignore \*.travis.yml \*.gitkeep \
                 -x \*test/\* \*.idea/\* \*coverage/\* \*.git/\* \*Tests/\*
mv Redmine.zip Redmine.alfredworkflow
composer update
