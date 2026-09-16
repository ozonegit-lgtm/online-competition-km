#!/bin/sh

set -eu

storage_root=/var/www/html/storage
app_storage_root="$storage_root/app"
private_root="$app_storage_root/private"
public_root="$app_storage_root/public"

# Uploaded files must be owned by the PHP-FPM user. Never follow symlinks
# while repairing a persistent volume.
for path in "$storage_root" "$app_storage_root" "$private_root" "$public_root"; do
    if [ -L "$path" ]; then
        echo "Upload storage path must not be a symbolic link: $path" >&2
        exit 1
    fi
done

mkdir -p "$private_root" "$public_root"

if [ -n "$(find "$private_root" "$public_root" -type l -print -quit)" ]; then
    echo 'Upload storage contains a symbolic link; refusing permission repair.' >&2
    exit 1
fi

mkdir -p "$private_root/submissions" "$private_root/knowledge-items/covers" "$private_root/knowledge-items/attachments"
if [ "$(id -u)" -eq 0 ]; then
    chown -R www-data:www-data "$private_root" "$public_root"
fi

find "$private_root" -type d -exec chmod 0700 {} +
find "$private_root" -type f -exec chmod 0600 {} +
find "$public_root" -type d -exec chmod 0755 {} +
find "$public_root" -type f -exec chmod 0644 {} +

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
