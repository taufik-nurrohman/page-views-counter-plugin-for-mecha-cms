Page Views Counter Plugin for Mecha CMS
=======================================

> This plugin will count the number of your page visits.

Your page visits will not be counted if you’re logged in.

The _Range_ field is used to add leading zeros as much as the value specified in the field. If the previous statistic shows the number of page views as **_35 Views_**, then after you put `5` on the field, the statistic will becomes **_00035 Views_**.

### Widget Installation

Put this widget code in your `article.php`, `page.php` or `index.php` file:

~~~ .php
<?php echo Widget::pageViews('page-slug', 'Views'); ?>
~~~

#### Example for `article.php`

~~~ .html
<footer class="post-footer">
  <?php echo Widget::pageViews($article->slug, 'Views'); ?>
</footer>
~~~

#### Example for `page.php`

~~~ .html
<footer class="post-footer">
  <?php echo Widget::pageViews($page->slug, 'Views'); ?>
</footer>
~~~

#### Example for `index.php`

~~~ .html
<?php foreach($articles as $article): ?>

…

<footer class="post-footer">
  <?php echo Widget::pageViews($article->slug, 'Views'); ?>
</footer>

…

<?php endforeach; ?>
~~~

### Advanced

Now you can display the page views statistic data that are stored in a folder other than `assets\lot\posts\article` and `assets\lot\posts\page` by entering the name of the folders in the `$slug` parameter:

~~~ .php
echo Widget::pageViews('2015/02/10/page-slug', 'Views');
~~~

This plugin will automatically stores the page views data of your custom pages (if any). For example, if you have a new public page with address ` {{url}}2015/02/10/page-slug `, then this plugin will automatically creates new folders in the `lot` folder to store the page views statistics by using the guidelines of the page path:

~~~ .no-highlight
lot
├── __2015/
│   └── __02/
│       └── __10/
│           └── __page-slug.txt
└── posts/
    ├── article/
    └── page/
~~~