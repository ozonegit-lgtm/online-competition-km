#!/bin/sh

set -eu

private_root=/var/www/html/storage/app/private

# Private uploads must be owned by the same user as PHP-FPM. Never follow
# symlinks while repairing an existing tree.
if [ -L /var/www/html/storage ] || [ -L /var/www/html/storage/app ] || [ -L "$private_root" ]; then
    echo 'Private storage must not be a symbolic link.' >&2
    exit 1
fi
mkdir -p "$private_root"
if [ -n "$(find "$private_root" -type l -print -quit)" ]; then
    echo 'Private storage contains a symbolic link; refusing permission repair.' >&2
    exit 1
fi
mkdir -p "$private_root/submissions" "$private_root/knowledge-items/covers" "$private_root/knowledge-items/attachments"
if [ "$(id -u)" -eq 0 ]; then
    chown -R www-data:www-data "$private_root"
fi
find "$private_root" -type d -exec chmod 0700 {} +
find "$private_root" -type f -exec chmod 0600 {} +

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

if [ "${APP_OPTIMIZE:-false}" = "true" ]; then
    if [ "${APP_ENV:-}" != "production" ]; then
        echo 'APP_OPTIMIZE=true is only allowed with APP_ENV=production.' >&2
        exit 1
    fi

    echo 'Rebuilding Laravel production caches...'
    php artisan optimize --no-ansi

    if [ "$(id -u)" -eq 0 ]; then
        chown -R www-data:www-data /var/www/html/storage/framework /var/www/html/bootstrap/cache
    fi
fi

exec /usr/local/bin/docker-php-entrypoint "$@"
