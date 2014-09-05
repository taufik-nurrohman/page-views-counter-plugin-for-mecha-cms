<?php


/**
 * Load the Configuration Data
 * ---------------------------
 */

$page_views_config = File::open(PLUGIN . DS . 'page-views-counter' . DS . 'states' . DS . 'config.txt')->unserialize();


/**
 * Inject the CSS File
 * -------------------
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet($config->url . '/cabinet/plugins/page-views-counter/shell/counter.css');
});


/**
 * Register the Page Views Widget
 * ------------------------------
 *
 * [1]. Widget::pageViews('article-slug', 'Page Views') // For article pages
 * [1]. Widget::pageViews('page-slug', 'Page Views') // For static pages
 *
 */

Widget::add('pageViews', function($slug = null, $text = 'Page Views') use($page_views_config) {
    $config = Config::get();
    $speak = Config::speak();
    $ranges = (int) $page_views_config['ranges'];
    if(is_null($slug) || ! isset($slug)) return '? ' . $text;
    $is_article_or_page = $config->page_type == 'page' ? 'pages' : 'articles';
    $file = PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . $is_article_or_page . DS . $slug . '.txt';
    $views = File::exist($file) ? File::open($file)->read() : 0;
    $views = trim($ranges) !== "" ? sprintf("%0" . $ranges . "d", $views) : $views;
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
    $is_article_or_page = $config->page_type == 'page' ? 'pages' : 'articles';
    if($is_article_or_page == 'articles') {
        $slug = isset($config->article->slug) ? $config->article->slug : false;
    } else {
        $slug = isset($config->page->slug) ? $config->page->slug : false;
    }
    if($slug !== false) {
        $file = PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . $is_article_or_page . DS . $slug . '.txt';
        $total_old = File::exist($file) ? (int) File::open($file)->read() : 0;
        if( ! Guardian::happy()) {
            File::write($total_old + 1)->saveTo($file, 0600);
        }
    }
});

// Rename file when the article slug is changed
Weapon::add('on_article_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Rename file when the page slug is changed
Weapon::add('on_page_update', function($old_data, $new_data) {
    if($new_data['data']['slug'] !== $old_data['data']['slug'] && $file = File::exist(PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')) {
        File::open($file)->renameTo($new_data['data']['slug'] . '.txt');
    }
});

// Delete file when the article is deleted
Weapon::add('on_article_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . 'articles' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Delete file when the page is deleted
Weapon::add('on_page_destruct', function($old_data, $new_data) {
    File::open(PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . 'pages' . DS . $old_data['data']['slug'] . '.txt')->delete();
});

// Show total page views in article and page manager
if(preg_match('#^' . $config->url . '\/' . $config->manager->slug . '\/(article|page)#', $config->url_current, $matches)) {
    Weapon::add($matches[1] . '_footer', function($page) use($config, $matches) {
        $path = PLUGIN . DS . 'page-views-counter' . DS . 'cargo' . DS . $matches[1] . 's' . DS . $page->slug . '.txt';
        $total = File::exist($path) ? (int) File::open($path)->read() : 0;
        echo '<span title="' . $total . ' ' . Config::speak('plugin_page_views_title_views') . '">' . $total . ' <i class="fa fa-eye"></i></span> &middot; ';
    }, 10);
}


/**
 * Create Backup
 * -------------
 */

Route::accept($config->manager->slug . '/plugin/page-views-counter/backup', function() use($config) {
    if( ! Guardian::happy()) {
        Shield::abort();
    }
    $name = Text::parse($config->title)->to_slug . '.plugin.page-views-counter.cargo_' . date('Y-m-d-H-i-s') . '.zip';
    Package::take(PLUGIN . DS . 'page-views-counter' . DS . 'cargo')->pack(ROOT . DS . $name);
    Guardian::kick($config->manager->slug . '/backup/send:' . $name);
});


/**
 * Plugin Updater
 * --------------
 */

Route::accept($config->manager->slug . '/plugin/page-views-counter/update', function() use($config, $speak) {
    if( ! Guardian::happy()) {
        Shield::abort();
    }
    if($request = Request::post()) {
        Guardian::checkToken($request['token']);
        File::write($request['css'])->saveTo(PLUGIN . DS . 'page-views-counter' . DS . 'shell' . DS . 'counter.css');
        unset($request['token']); // Remove token from request array
        unset($request['css']); // Remove CSS from request array
        File::serialize($request)->saveTo(PLUGIN . DS . 'page-views-counter' . DS . 'states' . DS . 'config.txt', 0600);
        Notify::success(Config::speak('notify_success_updated', array($speak->plugin)));
        Guardian::kick(dirname($config->url_current));
    }
});