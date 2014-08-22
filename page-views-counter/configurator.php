<form class="form-plugin" action="<?php echo $config->url_current; ?>/update" method="post">
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <?php

  $page_views_config = File::open(PLUGIN . DS . 'page-views-counter' . DS . 'states' . DS . 'config.txt')->unserialize();
  $page_views_css = File::open(PLUGIN . DS . 'page-views-counter' . DS . 'shell' . DS . 'counter.css')->read();

  ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views_title_css; ?></span>
    <span class="grid span-5"><textarea name="css" class="textarea-block code"><?php echo Text::parse(Guardian::wayback('css', $page_views_css))->to_encoded_html; ?></textarea></span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->plugin_page_views_title_ranges; ?></span>
    <span class="grid span-5"><input name="ranges" type="number" value="<?php echo Guardian::wayback('ranges', $page_views_config['ranges']); ?>"></span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5"><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button> <a class="btn btn-construct" href="<?php echo $config->url . '/' . $config->manager->slug; ?>/plugin/page-views-counter/backup"><i class="fa fa-download"></i> Create Backup</a></span>
  </div>
</form>