<?php


/**
 * Load the Configuration Data
 * ---------------------------
 */

$page_views_config = File::open(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'config.txt')->unserialize();


/**
 * Inject the CSS File
 * -------------------
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet('cabinet/plugins/' . basename(__DIR__) . '/shell/counter.css');
});


/**
 * Register the Page Views Widget
 * ------------------------------
 *
 * [1]. Widget::pageViews('article-slug', 'Page Views') // For article pages
 * [2]. Widget::pageViews('page-slug', 'Page Views') // For static pages
 * [3]. Widget::pageViews('new-folder/page-slug', 'Page Views') // For custom pages
 *
 */

Widget::add('pageViews', function($slug = "", $text = 'Page Views') use($page_views_config) {
    $config = Config::get();
    $speak = Config::speak();
    $ranges = (int) $page_views_config['ranges'];
    $slug = str_replace('/', DS, $slug);
    if($config->page_type == 'article') {
        $path = 'articles' . DS . $slug . '.txt';
    } elseif($config->page_type == 'page') {
        $path = 'pages' . DS . $slug . '.txt';
    } else {
        $path = $slug . '.txt';
    }
    $views = (string) File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . $path)->read('0');
    $views = trim($ranges) !== "" ? sprintf('%0' . $ranges . 'd', $views) : $views;
    $views_html = "";
    $views_number = str_split($views, 1);
    $position = 1;
    foreach($views_number as $number) {
        $views_html .= '<span class="i i-pos-' . $position . ' i-char-' . $number . '">' . $number . '</span>';
        $position++;
    }
    return '<span class="page-views-counter"><span class="page-views-total i-group">' . $views_html . '</span> <span class="page-views-label">' . $text . '</span></span>';
});


/**
 * The Page Views Counter
 * ----------------------
 */

Weapon::add('shield_before', function() {
    $config = Config::get();
    if($config->page_type == 'article' && isset($config->article->slug)) {
        $path = 'articles' . DS . $config->article->slug . '.txt';
    } elseif($config->page_type == 'page' && isset($config->page->slug)) {
        $path = 'pages' . DS . $config->page->slug . '.txt';
    } else {
        $path = str_replace('/', DS, $config->url_path) . '.txt';
    }
    $file = PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . ($path === '.txt' ? Text::parse($config->host, '->slug_moderate') . '.txt' : $path);
    $total_old = (int) File::open($file)->read(0);
    if( ! Guardian::happy() && $config->page_type != '404') {
        File::write($total_old + 1)->saveTo($file, 0600);
    }
});

// Rename file on article slug change
Weapon::add('on_article_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Rename file on page slug change
Weapon::add('on_page_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Delete file on article destruct
Weapon::add('on_article_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Delete file on page destruct
Weapon::add('on_page_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Show total page views in article manager page
Weapon::add('article_footer', function($article) use($config) {
    if($config->page_type == 'manager') {
        $total = (int) File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'articles' . DS . $article->slug . '.txt')->read(0);
        echo '<span title="' . $total . ' ' . Config::speak('plugin_page_views_title_views') . '">' . $total . ' <i class="fa fa-eye"></i></span> &middot; ';
    }
}, 10);

// Show total page views in page manager page
Weapon::add('page_footer', function($page) use($config) {
    if($config->page_type == 'manager') {
        $total = (int) File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . 'pages' . DS . $page->slug . '.txt')->read(0);
        echo '<span title="' . $total . ' ' . Config::speak('plugin_page_views_title_views') . '">' . $total . ' <i class="fa fa-eye"></i></span> &middot; ';
    }
}, 10);


/**
 * Create Backup
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/' . basename(__DIR__) . '/backup', function() use($config) {
    if( ! Guardian::happy()) {
        Shield::abort();
    }
    $name = Text::parse($config->title, '->slug') . '.cabinet.plugins.' . basename(__DIR__) . '.cargo_' . date('Y-m-d-H-i-s') . '.zip';
    Package::take(PLUGIN . DS . basename(__DIR__) . DS . 'cargo')->pack(ROOT . DS . $name);
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/' . basename(__DIR__) . '/update', function() use($config, $speak) {
    if( ! Guardian::happy()) {
        Shield::abort();
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::write($request['css'])->saveTo(PLUGIN . DS . basename(__DIR__) . DS . 'shell' . DS . 'counter.css');
        unset($request['token']); // Remove token from request array
        unset($request['css']); // Remove CSS from request array
        File::serialize($request)->saveTo(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
        Guardian::kick(dirname($config->url_current));
    }
});