#!/bin/bash

cd "$(dirname "$0")"

for f in $(ls tests/*.test.php)
do
    printf "\n########################\ntesting $f\n"
    ./phpunit-5.7.phar $f || exit 1
done
