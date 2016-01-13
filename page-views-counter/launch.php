<?php


/**
 * Load the Configuration Data
 * ---------------------------
 */

$page_views_config = File::open(__DIR__ . DS . 'states' . DS . 'config.txt')->unserialize();


/**
 * Inject the CSS File
 * -------------------
 */

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'counter.css');
});


/**
 * Register the Page Views Widget
 * ------------------------------
 *
 * [1]. Widget::pageViews('article-slug', 'Views') // For article pages
 * [2]. Widget::pageViews('page-slug', 'Views') // For static pages
 * [3]. Widget::pageViews('foo/bar/page-slug', 'Views') // For custom pages
 *
 */

Widget::add('pageViews', function($slug = "", $text = null) use($speak, $page_views_config) {
    $config = Config::get();
    $speak = Config::speak();
    if(is_null($text)) $text = $speak->plugin_page_views_title_views;
    $ranges = (int) $page_views_config['ranges'];
    $slug = str_replace(array(DS, ':'), array(DS . '__', '--'), File::path($slug));
    if(Mecha::walk(array('article', 'index', 'tag', 'archive', 'search', ""))->has($config->page_type)) {
        $path = 'posts' . DS . 'article' . DS . $slug . '.txt';
    } else if($config->page_type === 'page') {
        $path = 'posts' . DS . 'page' . DS . $slug . '.txt';
    } else {
        $path = '__' . $slug . '.txt';
    }
    $views = (string) File::open(__DIR__ . DS . 'assets' . DS . 'lot' . DS . $path)->read(0);
    $views = trim($ranges) !== "" ? sprintf('%0' . $ranges . 'd', $views) : $views;
    $views_html = "";
    $views_count = str_split($views, 1);
    $position = 1;
    foreach($views_count as $count) {
        $views_html .= '<span class="i i-pos-' . $position . ' i-char-' . $count . '">' . $count . '</span>';
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
    if( ! Guardian::happy() && ! Mecha::walk(array('manager', '404'))->has($config->page_type) && $config->offset === 1) {
        if($config->page_type === 'article' && isset($config->article->slug)) {
            $path = 'posts' . DS . 'article' . DS . $config->article->slug . '.txt';
        } else if($config->page_type === 'page' && isset($config->page->slug)) {
            $path = 'posts' . DS . 'page' . DS . $config->page->slug . '.txt';
        } else {
            $path = '__' . str_replace(array(DS, ':'), array(DS . '__', '--'), File::path($config->url_path)) . '.txt';
        }
        $file = __DIR__ . DS . 'assets' . DS . 'lot' . DS . ($path === '.txt' ? Text::parse($config->host, '->safe_file_name') . '.txt' : $path);
        $total_old = (int) File::open($file)->read(0);
        File::write($total_old + 1)->saveTo($file, 0600);
    }
});