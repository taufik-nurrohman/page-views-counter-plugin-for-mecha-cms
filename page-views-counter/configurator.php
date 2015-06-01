<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <?php

  $page_views_config = File::open(PLUGIN . DS . basename(__DIR__) . DS . 'states' . DS . 'config.txt')->unserialize();
  $page_views_css = File::open(PLUGIN . DS . basename(__DIR__) . DS . 'shell' . DS . 'counter.css')->read();

  ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views_title_css; ?></span>
    <span class="grid span-5"><?php echo Form::textarea('css', $page_views_css, null, array('class' => array('textarea-block', 'code'))); ?></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views_title_ranges; ?></span>
    <span class="grid span-5"><?php echo Form::number('ranges', $page_views_config['ranges']); ?></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5"><?php echo Jot::button('action', $speak->update) . (file_exists(PLUGIN . DS . basename(__DIR__) . DS . 'cargo') ? ' ' . Jot::btn('construct:download', $speak->plugin_page_views_title_create_backup, $config->manager->slug . '/plugin/' . basename(__DIR__) . '/backup') : ""); ?></span>
  </div>
</form>