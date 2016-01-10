<?php

// Rename file on article/page slug change
Weapon::add(array('on_article_update', 'on_page_update'), function($G, $P) use($segment) {
    $G = $G['data']['slug'];
    $P = $P['data']['slug'];
    if($P !== $G && $file = File::exist(__DIR__ . DS . 'assets' . DS . 'lot' . DS . 'posts' . DS . $segment . DS . $G . '.txt')) {
        File::open($file)->renameTo($P . '.txt');
    }
});

// Delete file on article/page destruct
Weapon::add(array('on_article_destruct', 'on_page_destruct'), function($G, $P) use($segment) {
    $G = $G['data']['slug'];
    $P = $P['data']['slug'];
    File::open(__DIR__ . DS . 'assets' . DS . 'lot' . DS . 'posts' . DS . $segment . DS . $G . '.txt')->delete();
});

// Show total page views in article/page manager page
Weapon::add(array('article_footer', 'page_footer'), function($post) use($speak, $segment) {
    $total = (int) File::open(__DIR__ . DS . 'assets' . DS . 'lot' . DS . 'posts' . DS . $segment . DS . $post->slug . '.txt')->read(0);
    echo '<span title="' . $total . ' ' . $speak->plugin_page_views_title_views . '">' . Jot::icon('eye') . ' ' . $total . '</span> &middot; ';
}, 10);


/**
 * Create Backup
 * -------------
 */

if(Plugin::exist('backup')) {
    Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/backup', function() use($config) {
        $name = Text::parse($config->title, '->slug') . '.lot.plugins.' . File::B(__DIR__) . '.assets.lot_' . date('Y-m-d-H-i-s') . '.zip';
        Package::take(__DIR__ . DS . 'assets' . DS . 'lot')->pack(ROOT . DS . $name);
        Guardian::kick($config->manager->slug . '/backup/send:' . $name);
    });
}


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::write($request['css'])->saveTo(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'counter.css');
        unset($request['token']); // Remove token from request array
        unset($request['css']); // Remove CSS from request array
        File::serialize($request)->saveTo(__DIR__ . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', $speak->plugin));
        Guardian::kick(File::D($config->url_current));
    }
});