<?php

$c_page_views = $config->states->{'plugin_' . md5(File::B(__DIR__))};
$c_page_views_css = File::open(__DIR__ . DS . 'assets' . DS . 'shell' . DS . 'page-views.css')->read();

?>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views->title->css; ?></span>
  <span class="grid span-5"><?php echo Form::textarea('css', $c_page_views_css, null, array('class' => array('textarea-block', 'textarea-expand', 'code'))); ?></span>
</label>
<label class="grid-group">
  <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views->title->range; ?></span>
  <span class="grid span-5"><?php echo Form::number('range', $c_page_views->range); ?></span>
</label>
<div class="grid-group">
  <span class="grid span-1"></span>
  <span class="grid span-5"><?php echo Jot::button('action', $speak->update) . (File::exist(__DIR__ . DS . 'assets' . DS . 'lot') && Plugin::exist('backup') ? ' ' . Jot::btn('construct:download', $speak->backup, $config->manager->slug . '/plugin/' . File::B(__DIR__) . '/backup') : ""); ?></span>
</div>