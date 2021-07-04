#!/usr/bin/env bash

docker run --rm -it -v "$PWD:/data" -w /data abtercms/phpdoc -d vendor/abterphp/framework/src -d vendor/abterphp/admin/src -d vendor/abterphp/website/src -d vendor/abterphp/files/src -d vendor/abterphp/contact/src -d vendor/abterphp/bootstrap4-website/src -d vendor/abterphp/propeller-admin/src -d vendor/abterphp/website-creative/src -t docs --title "AbterPHP" --sourcecode --defaultpackagename AbterPhp
