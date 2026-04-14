@servers([
    'localhost' => '127.0.0.1',
    'production' => 'deploy@your-server-ip',
])

@story('deploy')
    prepare
    release
@endstory

@task('prepare', ['on' => 'localhost'])
    php artisan test

    {{-- Rigenera la doc Scribe e committa gli artefatti --}}
    {{-- php artisan scribe:generate --}}
    {{-- git add -A && git commit -m "Regenerate docs" --}}

    git push origin main
@endtask

@task('release', ['on' => 'production'])
    cd /var/www/bookshelf

    php artisan down

    git pull origin main

    composer install --no-dev --no-interaction --prefer-dist

    php artisan migrate --force
    php artisan optimize

    php artisan up
@endtask
