#!/bin/sh

set -eu

runtime_directories='
/var/www/html/storage/framework/sessions
/var/www/html/storage/framework/views
/var/www/html/storage/framework/cache
/var/www/html/storage/framework/cache/data
/var/www/html/storage/logs
/var/www/html/bootstrap/cache
'

echo 'Preparing Laravel runtime directories...'

for directory in $runtime_directories; do
    if [ -L "$directory" ]; then
        echo "Runtime path must not be a symbolic link: $directory" >&2
        exit 1
    fi

    mkdir -p "$directory"

    if [ ! -d "$directory" ]; then
        echo "Failed to prepare runtime directory: $directory" >&2
        exit 1
    fi
done

if [ "$(id -u)" -eq 0 ]; then
    for directory in $runtime_directories; do
        chown -R www-data:www-data "$directory"
    done
fi

for directory in $runtime_directories; do
    chmod -R ug+rwX "$directory"

    if [ ! -w "$directory" ]; then
        echo "Runtime directory is not writable: $directory" >&2
        exit 1
    fi
done

if [ ! -x /usr/local/bin/docker-php-entrypoint ]; then
    echo 'Official PHP Docker entrypoint is missing or not executable.' >&2
    exit 1
fi

echo 'Laravel runtime directories are ready.'

exec /usr/local/bin/docker-php-entrypoint "$@"
