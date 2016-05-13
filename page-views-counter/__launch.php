<?php

$r = __DIR__ . DS . 'assets' . DS . 'lot' . DS . 'posts' . DS;
foreach(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
    $v = File::B($v);
    // Rename file on post slug change
    Weapon::add('on_' . $v . '_update', function($G, $P) use($v, $r) {
        $a = $G['data']['slug'];
        $b = $G['data']['slug'];
        if($a !== $b && $log = File::exist($r . $v . DS . $a . '.txt')) {
            File::open($log)->renameTo($b . '.txt');
        }
    });
    // Delete file on post destruct
    Weapon::add('on_' . $v . '_destruct', function($G, $P) use($v, $r) {
        File::open($r . $v . DS . $G['data']['slug'] . '.txt')->delete();
    });
    // Show total page view(s) in post manager page
    Weapon::add($v . '_footer', function($post) use($speak, $v, $r) {
        $count = (int) File::open($r . $v . DS . $post->slug . '.txt')->read(0);
        echo '<span title="' . $count . ' ' . $speak->plugin_page_views->title->views . '">' . Jot::icon('eye') . ' ' . $count . '</span> &middot; ';
    }, 10);
}

if(Plugin::exist('backup')) {
    Route::accept($config->manager->slug . '/plugin/(' . File::B(__DIR__) . ')/backup', function($s = "") use($config) {
        $name = Text::parse($config->title, '->slug') . '.lot.plugins.' . $s . '.assets.lot_' . date('Y-m-d-H-i-s') . '.zip';
        Package::take(__DIR__ . DS . 'assets' . DS . 'lot')->pack(ROOT . DS . $name);
        Guardian::kick($config->manager->slug . '/backup/send:' . $name);
    });
}


/**
 * Plugin Updater
 * --------------
 */

Route::over($config->manager->slug . '/plugin/' . File::B(__DIR__) . '/update', function() use($config, $speak) {
    File::write(Request::post('css'))->saveTo(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'page-views.css');
    unset($_POST['css']);
});