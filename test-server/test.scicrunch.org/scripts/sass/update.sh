#!/bin/bash

cd "$(dirname "$0")"

for f in $(ls *scss); do
    if [[ $f == _* ]]; then
        continue
    fi
    newname=$(echo $f | sed 's/\.scss$/\.css/')
    sass $f > ../../css/$newname
done
