<?php

$c_page_views_counter = $config->states->{'plugin_' . md5(File::B(__DIR__))};

Weapon::add('shell_after', function() use($config) {
    echo Asset::stylesheet(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'page-views.css', "", 'shell/plugin.page-views.min.css');
});

function do_locate_page_views($path) {
    $config = Config::get();
    $f = false;
    foreach(glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
        $v = File::B($v);
        if($config->page_type === $v || (isset($config->{$v . 's'}) && $config->{$v . 's'} !== false)) {
            $f = 'posts' . DS . $v . DS . $path . '.txt';
            break;
        }
    }
    if( ! $f) {
        $f = '__' . str_replace(array('/', ':'), array(DS . '__', '--'), $path) . '.txt';
    }
    return __DIR__ . DS . 'assets' . DS . 'lot' . DS . $f;
}

function do_set_page_views() {
    $config = Config::get();
    $s = $config->page_type;
    if(isset($config->{$s}->path) && isset($config->{$s}->slug) && file_exists($config->{$s}->path)) {
        $f = 'posts' . DS . $s . DS . $config->{$s}->slug . '.txt';
    } else {
        $f = '__' . str_replace(array('/', ':'), array(DS . '__', '--'), $config->url_path) . '.txt';
    }
    if($config->offset === 1 && $s !== '404' && ! Guardian::happy()) {
        $path = __DIR__ . DS . 'assets' . DS . 'lot' . DS . $f;
        $i = File::open($path)->read(0);
        File::write($i + 1)->saveTo($path, 0600);
    }
}

function do_get_page_views($path, $text = false, $html = true) {
    global $speak, $c_page_views_counter;
    $text = $text !== false ? $text : $speak->plugin_page_views->title->views;
    if(strpos($path, PLUGIN . DS . File::B(__DIR__) . DS) !== 0) {
        $path = do_locate_page_views($path);
    }
    $r = $c_page_views_counter->range;
    $v = (string) File::open($path)->read(0);
    $v = trim($r) !== "" && $r > 0 ? sprintf('%0' . $r . 'd', $v) : $v;
    if($html) {
        $i = 1;
        $html  = '<span class="page-views-counter">';
        $html .= '<span class="page-views-counter-count i-group">';
        foreach(str_split($v, 1) as $s) {
            $html .= '<span class="i i-pos-' . $i . ' i-char-' . $s . '">' . $s . '</span>';
            ++$i;
        }
        $html .= '</span> ';
        $html .= '<span class="page-views-counter-label">' . $text . '</span>';
        return $html . '</span>';
    }
    return $v . ' ' . $text;
}

Weapon::add('shield_before', 'do_set_page_views');
Widget::add('pageViews', 'do_get_page_views');