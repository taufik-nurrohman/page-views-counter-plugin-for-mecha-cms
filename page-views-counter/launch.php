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
    $slug = File::path($slug);
    if($config->page_type === 'article') {
        $path = 'articles' . DS . $slug . '.txt';
    } elseif($config->page_type === 'page') {
        $path = 'pages' . DS . $slug . '.txt';
    } else {
        $path = '__' . str_replace(array(DS, ':'), array(DS . '__', '--'), $slug) . '.txt';
    }
    $views = (string) File::open(PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . $path)->read('0');
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
    if($config->page_type === 'article' && isset($config->article->slug)) {
        $path = 'articles' . DS . $config->article->slug . '.txt';
    } elseif($config->page_type === 'page' && isset($config->page->slug)) {
        $path = 'pages' . DS . $config->page->slug . '.txt';
    } else {
        $path = '__' . str_replace(array(DS, ':'), array(DS . '__', '--'), File::path($config->url_path)) . '.txt';
    }
    $file = PLUGIN . DS . basename(__DIR__) . DS . 'cargo' . DS . ($path === '.txt' ? Text::parse($config->host, '->safe_file_name') . '.txt' : $path);
    $total_old = (int) File::open($file)->read(0);
    if( ! Guardian::happy() && $config->page_type !== '404') {
        File::write($total_old + 1)->saveTo($file, 0600);
    }
});