<?php

// Rename file on article slug change
Weapon::add('on_article_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Rename file on page slug change
Weapon::add('on_page_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Delete file on article destruct
Weapon::add('on_article_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Delete file on page destruct
Weapon::add('on_page_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Show total page views in article manager page
Weapon::add('article_footer', function($article) use($config) {
    $total = (int) File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'articles' . DS . $article->slug . '.txt')->read(0);
    echo '<span title="' . $total . ' ' . Config::speak('plugin_page_views_title_views') . '">' . $total . ' ' . Jot::icon('eye') . '</span> &middot; ';
}, 10);

// Show total page views in page manager page
Weapon::add('page_footer', function($page) use($config) {
    $total = (int) File::open(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo' . DS . 'pages' . DS . $page->slug . '.txt')->read(0);
    echo '<span title="' . $total . ' ' . Config::speak('plugin_page_views_title_views') . '">' . $total . ' ' . Jot::icon('eye') . '</span> &middot; ';
}, 10);


/**
 * Create Backup
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/backup', function() use($config) {
    $name = Text::parse($config->title, '->slug') . '.cabinet.plugins.' . File::B(__DIR__) . '.assets.cargo_' . date('Y-m-d-H-i-s') . '.zip';
    Package::take(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'cargo')->pack(ROOT . DS . $name);
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::write($request['css'])->saveTo(PLUGIN . DS . File::B(__DIR__) . DS . 'assets' . DS . 'shell' . DS . 'counter.css');
        unset($request['token']); // Remove token from request array
        unset($request['css']); // Remove CSS from request array
        File::serialize($request)->saveTo(PLUGIN . DS . File::B(__DIR__) . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});