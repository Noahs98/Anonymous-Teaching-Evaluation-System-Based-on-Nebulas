<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'my_project');

// Project repository
set('repository', '');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);

// Hosts

host('project.com')
    ->set('deploy_path', '~/{{application}}');

// Tasks
task('build', function () {
    set('deploy_path', __DIR__ . '/.build');
    invoke('deploy:prepare');
    invoke('deploy:release');
    invoke('deploy:update_code');
    invoke('deploy:vendors');
    invoke('deploy:symlink');
})->local();

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');
