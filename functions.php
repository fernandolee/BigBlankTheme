<?php
/**
 * Big Blank functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link http://codex.wordpress.org/Theme_Development
 * @link http://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * @link http://codex.wordpress.org/Plugin_API
 *
 */
/**
 * Set up the content width value based on the theme's design.
 *
 * @see bigblank_content_width()
 *
 */
if (!isset($content_width)) {
    $content_width = 800;
}
/**
 * Big Blank only works in WordPress 3.8 or later.
 */
if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
    require get_template_directory() . '/inc/back-compat.php';
}
if (!function_exists('bigblank_setup')) :

    /**
     * Big Blank setup.
     *
     * Set up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support post thumbnails.
     *
     */
    function bigblank_setup() {
        /*
         * Make Big Blank available for translation.
         *
         * Translations can be added to the /languages/ directory.
         * If you're building a theme based on Big Blank, use a find and
         * replace to change 'bigblank' to the name of your theme in all
         * template files.
         */
        load_theme_textdomain('bigblank', get_template_directory() . '/languages');
        // This theme styles the visual editor to resemble the theme style.
        add_editor_style(array('css/editor-style.css'));
        // Add RSS feed links to <head> for posts and comments.
        add_theme_support('automatic-feed-links');
        // Enable support for Post Thumbnails, and declare two sizes.
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(672, 372, true);
        add_image_size('bigblank-full-width', 1038, 576, true);
        // This theme uses wp_nav_menu() in two locations.
        register_nav_menus(array(
            'primary' => __('Top primary menu', 'bigblank'),
            'secondary' => __('Secondary menu in left sidebar', 'bigblank'),
        ));
        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form', 'comment-form', 'comment-list',
        ));
        // This theme uses its own gallery styles.
        add_filter('use_default_gallery_style', '__return_false');
    }

endif; // bigblank_setup
add_action('after_setup_theme', 'bigblank_setup');

/**
 * Adjust content_width value for image attachment template.
 *
 *
 * @return void
 */
function bigblank_content_width() {
    if (is_attachment() && wp_attachment_is_image()) {
        $GLOBALS['content_width'] = 960;
    }
}

add_action('template_redirect', 'bigblank_content_width');

/**
 * Let's remove some code and cleanup <head>
 */
function be_head_cleanup() {
    /**
     * remove Really Simple Discoverability; Roll it in if you want to use 
     * Weblog Clients that use XML-RPC Support
     * @link http://codex.wordpress.org/XML-RPC_Support
     */
    remove_action('wp_head', 'rsd_link');
    // remove Windows Live Writer Manifest link
    remove_action('wp_head', 'wlwmanifest_link');
    // remove WordPress version meta
    remove_action('wp_head', 'wp_generator');
}

add_action('init', 'be_head_cleanup');

/**
 * Remove recent comments inline style form WP <head>
 * .recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}
 * @global type $wp_widget_factory
 */
function be_remove_recent_comments_style() {
    global $wp_widget_factory;
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
}

add_action('widgets_init', 'be_remove_recent_comments_style');

/**
 * Remove version from CSS and JS files for Caching
 * @param string|array $src Query key or keys to remove.
 * @return string New URL query string.
 */
function bigblank_remove_wp_ver_css_js($src) {
    if (strpos($src, 'ver='))
        $src = remove_query_arg('ver', $src);
    return $src;
}

add_filter('style_loader_src', 'bigblank_remove_wp_ver_css_js');
add_filter('script_loader_src', 'bigblank_remove_wp_ver_css_js');

/**
 * Register three Big Blank widget areas.
 *
 *
 * @return void
 */
function bigblank_widgets_init() {
    require get_template_directory() . '/inc/widgets.php';
    register_widget('Big_Blank_Ephemera_Widget');
    register_sidebar(array(
        'name' => __('Primary Sidebar', 'bigblank'),
        'id' => 'sidebar-1',
        'description' => __('Main sidebar that appears on the left.', 'bigblank'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
    register_sidebar(array(
        'name' => __('Content Sidebar', 'bigblank'),
        'id' => 'sidebar-2',
        'description' => __('Additional sidebar that appears on the right.', 'bigblank'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
    register_sidebar(array(
        'name' => __('Footer Widget Area', 'bigblank'),
        'id' => 'sidebar-3',
        'description' => __('Appears in the footer section of the site.', 'bigblank'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
}

add_action('widgets_init', 'bigblank_widgets_init');

/**
 * Enqueue scripts and styles for the front end.
 *
 * Read more about wp_register_script at: 
 * @link http://codex.wordpress.org/Function_Reference/wp_register_script
 * @return void
 */
function bigblank_scripts() {

    // Add Genericons font, used in the main stylesheet.
    wp_enqueue_style('genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.0.2');
    // Load our main stylesheet.
    wp_enqueue_style('style', get_stylesheet_uri(), array('genericons'));
    // Load the Internet Explorer specific stylesheet.
    wp_enqueue_style('ie', get_template_directory_uri() . '/css/ie.css', array('style', 'genericons'), '20140222');
    wp_style_add_data('ie', 'conditional', 'lt IE 9');

    // jQuery.js
    // 1. load the latest jQuery from theme library
    //    wp_deregister_script('jquery');
    //    wp_register_script('jquery', get_template_directory_uri() . '/js/jquery.js', false, '2.1.0', true);
    // 2. load from Google CDN        
    //    wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js', false, false, true);
    // 3. load from WP included library, Loading jQuery in footer sometimes causes 
    //    for some plugins to not work since they do not register jQuery as dependancy 
    //    wp_register_script('jquery', false, false, false, true);
    // 4. or do nothing and jQuery will load from current WordPress install
    
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply', false, false, false, true);
    }
    wp_enqueue_script('scripts', get_template_directory_uri() . '/js/scripts.min.js', array('jquery'), '20140222', true);
    wp_enqueue_script('main', get_template_directory_uri() . '/js/main.js', array('jquery', 'scripts'), '20140222', true);
}

add_action('wp_enqueue_scripts', 'bigblank_scripts');

/**
 *  filter <p> tags wrapping images
 */
function filter_ptags_on_images($content) {
    return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}

add_filter('the_content', 'filter_ptags_on_images');

// set post excerpt length
function be_excerpt_length($length) {
    return 140;
}

add_filter('excerpt_length', 'be_excerpt_length');



if (!function_exists('bigblank_the_attached_image')) :

    /**
     * Print the attached image with a link to the next attached image.
     *
     *
     * @return void
     */
    function bigblank_the_attached_image() {
        $post = get_post();
        /**
         * Filter the default Big Blank attachment size.
         *
         *
         * @param array $dimensions {
         *     An array of height and width dimensions.
         *
         *     @type int $height Height of the image in pixels. Default 810.
         *     @type int $width  Width of the image in pixels. Default 810.
         * }
         */
        $attachment_size = apply_filters('bigblank_attachment_size', array(810, 810));
        $next_attachment_url = wp_get_attachment_url();
        /*
         * Grab the IDs of all the image attachments in a gallery so we can get the URL
         * of the next adjacent image in a gallery, or the first image (if we're
         * looking at the last image in a gallery), or, in a gallery of one, just the
         * link to that image file.
         */
        $attachment_ids = get_posts(array(
            'post_parent' => $post->post_parent,
            'fields' => 'ids',
            'numberposts' => -1,
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'order' => 'ASC',
            'orderby' => 'menu_order ID',
        ));
        // If there is more than 1 attachment in a gallery...
        if (count($attachment_ids) > 1) {
            foreach ($attachment_ids as $attachment_id) {
                if ($attachment_id == $post->ID) {
                    $next_id = current($attachment_ids);
                    break;
                }
            }
            // get the URL of the next image attachment...
            if ($next_id) {
                $next_attachment_url = get_attachment_link($next_id);
            }
            // or get the URL of the first image attachment.
            else {
                $next_attachment_url = get_attachment_link(array_shift($attachment_ids));
            }
        }
        printf('<a href="%1$s" rel="attachment">%2$s</a>', esc_url($next_attachment_url), wp_get_attachment_image($post->ID, $attachment_size)
        );
    }

endif;
if (!function_exists('bigblank_list_authors')) :

    /**
     * Print a list of all site contributors who published at least one post.
     *
     *
     * @return void
     */
    function bigblank_list_authors() {
        $contributor_ids = get_users(array(
            'fields' => 'ID',
            'orderby' => 'post_count',
            'order' => 'DESC',
            'who' => 'authors',
        ));
        foreach ($contributor_ids as $contributor_id) :
            $post_count = count_user_posts($contributor_id);
            // Move on if user has not published a post (yet).
            if (!$post_count) {
                continue;
            }
            ?>
            <div class="contributor">
                <div class="contributor-info">
                    <div class="contributor-avatar"><?php echo get_avatar($contributor_id, 132); ?></div>
                    <div class="contributor-summary">
                        <h2 class="contributor-name"><?php echo get_the_author_meta('display_name', $contributor_id); ?></h2>
                        <p class="contributor-bio">
                            <?php echo get_the_author_meta('description', $contributor_id); ?>
                        </p>
                        <a class="contributor-posts-link" href="<?php echo esc_url(get_author_posts_url($contributor_id)); ?>">
                            <?php printf(_n('%d Article', '%d Articles', $post_count, 'bigblank'), $post_count); ?>
                        </a>
                    </div><!-- .contributor-summary -->
                </div><!-- .contributor-info -->
            </div><!-- .contributor -->
            <?php
        endforeach;
    }

endif;

/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Index views.
 * 3. Full-width content layout.
 * 4. Presence of footer widgets.
 * 5. Single views.
 *
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 */
function bigblank_body_classes($classes) {
    if (is_multi_author()) {
        $classes[] = 'group-blog';
    }
    if (is_archive() || is_search() || is_home()) {
        $classes[] = 'list-view';
    }
    if ((!is_active_sidebar('sidebar-2') ) || is_page_template('page-templates/full-width.php') || is_page_template('page-templates/contributors.php') || is_attachment()) {
        $classes[] = 'full-width';
    }
    if (is_active_sidebar('sidebar-3')) {
        $classes[] = 'footer-widgets';
    }
    if (is_singular() && !is_front_page()) {
        $classes[] = 'singular';
    }
    return $classes;
}

add_filter('body_class', 'bigblank_body_classes');

/**
 * Extend the default WordPress post classes.
 *
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 *
 *
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 */
function bigblank_post_classes($classes) {
    if (!post_password_required() && has_post_thumbnail()) {
        $classes[] = 'has-post-thumbnail';
    }
    return $classes;
}

add_filter('post_class', 'bigblank_post_classes');

/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function bigblank_wp_title($title, $sep) {
    global $paged, $page;
    if (is_feed()) {
        return $title;
    }
    // Add the site name.
    $title .= get_bloginfo('name');
    // Add the site description for the home/front page.
    $site_description = get_bloginfo('description', 'display');
    if ($site_description && ( is_home() || is_front_page() )) {
        $title = "$title $sep $site_description";
    }
    // Add a page number if necessary.
    if ($paged >= 2 || $page >= 2) {
        $title = "$title $sep " . sprintf(__('Page %s', 'bigblank'), max($paged, $page));
    }
    return $title;
}

add_filter('wp_title', 'bigblank_wp_title', 10, 2);
// Custom template tags for this theme.
require get_template_directory() . '/inc/template-tags.php';
// Add Theme Customizer functionality.
require get_template_directory() . '/inc/customizer.php';
