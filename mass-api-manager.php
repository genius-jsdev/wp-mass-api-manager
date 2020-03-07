<?php

/*
Plugin Name: Mass Api Manager
Plugin URI: 
Description: 
Author: Sam Khan
Developer: Gaudin Canddy
Version: 1.0.0
Author URI: 
Tested up to: 
*/

if ((isset($_GET['page']) && ((!empty($_GET['page']) || ('mass-api-manager' === $_GET['page'])))) || (isset($_GET['tab']) && ((!empty($_GET['tab']) || ('other_plugins' === $_GET['tab']))))) {
    add_action('admin_enqueue_scripts', 'mam_load_my_script');
}

function mam_load_my_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-dialog');
    // wp_enqueue_script('jquery-slim-js', 'https://code.jquery.com/jquery-3.4.1.slim.min.js', array(), 'all');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array(), 'all');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js', array(), 'all');
    // wp_enqueue_script('admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js');
    // wp_enqueue_script('public-js', plugin_dir_url(__FILE__) . 'assets/js/public.js');
    wp_enqueue_script('jquery-colorpicker-js', plugin_dir_url(__FILE__) . 'assets/js/jquery.colorpickersliders.js');
    wp_enqueue_script('bs4-mam-js', plugin_dir_url(__FILE__) . 'assets/notification/bs4.pop.js');
    wp_enqueue_script('custom-mam-js', plugin_dir_url(__FILE__) . 'assets/js/custom.min.js', array(), 'all');
    wp_localize_script('custom-mam-js', 'mam_magicalData', array('nonce' => wp_create_nonce('wp_rest')));
}

if ((isset($_GET['page']) && !empty($_GET['page']) && ('mass-api-manager' === $_GET['page'])) || (isset($_GET['page']) && !empty($_GET['page']) && ('mass-api-manager' === $_GET['page']) && isset($_GET['tab']) && !empty($_GET['tab']) && ('about' === $_GET['tab']))) {
    add_action('admin_enqueue_scripts', 'mam_styles');
}

function mam_styles()
{
    wp_enqueue_style('bootstrap-mam-css', "https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cerulean/bootstrap.min.css");
    wp_enqueue_style('admin-mam-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
    wp_enqueue_style('bs4-mam-css', plugin_dir_url(__FILE__) . 'assets/notification/bs4.pop.css');
    wp_enqueue_style('style-mam-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}





// create a scheduled event (if it does not exist already)
function cronstarter_activation()
{
    if (!wp_next_scheduled('mamcronjob')) {
        wp_schedule_event(time(), 'daily', 'mamcronjob');
    }
}
// and make sure it's called whenever WordPress loads
add_action('wp', 'cronstarter_activation');

// unschedule event upon plugin deactivation
function cronstarter_deactivate()
{
    // find out when the last event was scheduled
    $timestamp = wp_next_scheduled('mamcronjob');
    // unschedule previous event if any
    wp_unschedule_event($timestamp, 'mamcronjob');
}
register_deactivation_hook(__FILE__, 'cronstarter_deactivate');

// here's the function we'd like to call with our cron job
function my_repeat_function()
{

    // do here what needs to be done automatically as per your schedule
    // in this example we're sending an email

    // components for our email
    // $recepients = 'you@example.com';
    // $subject = 'Hello from your Cron Job';
    // $message = 'This is a test mail sent by WordPress automatically as per your schedule.';

    // // let's send it 
    // mail($recepients, $subject, $message);
}

// hook that function onto our scheduled event:
add_action('mamcronjob', 'my_repeat_function');

// add custom interval
// function cron_add_minute( $schedules ) {
// 	// Adds once every minute to the existing schedules.
//     $schedules['everyminute'] = array(
// 	    'interval' => 60,
// 	    'display' => __( 'Once Every Minute' )
//     );
//     return $schedules;
// }
// add_filter( 'cron_schedules', 'cron_add_minute' );

// create a scheduled event (if it does not exist already)
// function cronstarter_activation() {
// 	if( !wp_next_scheduled( 'mycronjob' ) ) {  
// 	   wp_schedule_event( time(), 'everyminute', 'mycronjob' );  
// 	}
// }
// // and make sure it's called whenever WordPress loads
// add_action('wp', 'cronstarter_activation');




function mam_pages_posts_creator()
{
    add_menu_page('Mass Api Manager', 'Mass Api Manager', 'manage_options', 'mass-api-manager', 'mam_create', plugin_dir_url(__FILE__) . 'assets/images/logo.png');
}

add_action('admin_menu', 'mam_pages_posts_creator');

add_shortcode('mam_video', function ($attr, $content) {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('custom-front-mam-js', plugin_dir_url(__FILE__) . 'assets/js/custom_front.js', array(), 'all');
    wp_enqueue_style('bootstrap-mam-css', "https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cerulean/bootstrap.min.css");
    wp_enqueue_style('style-front-mam-css', plugin_dir_url(__FILE__) . 'assets/css/style_front.css');

    $video_id = $attr['id'];
    return '<iframe class="col-md-12 mam_campaign_main_container" src="https://www.youtube.com/embed/' . $video_id . '"></iframe>';
});

add_shortcode('mam_tag', function ($attr, $content) {
    wp_enqueue_script('custom-front-mam-js', plugin_dir_url(__FILE__) . 'assets/js/custom_front.js', array(), 'all');
    wp_enqueue_style('bootstrap-mam-css', "https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cerulean/bootstrap.min.css");
    wp_enqueue_style('admin-mam-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
    wp_enqueue_style('style-front-mam-css', plugin_dir_url(__FILE__) . 'assets/css/style_front.css');

    $post_id = $attr['id'];
    $post_tags = get_the_tags($post_id);
    $result = '';
    $separator = ' | ';
    if (!empty($post_tags)) {
        foreach ($post_tags as $tag) {
            $result .= '<a href="' . get_tag_link($tag->term_id) . '">' . $tag->name . '</a>' . $separator;
        }
        return trim($result, $separator);
    }
    return '';
});
add_shortcode('mam_bing_images', function ($attr, $content) {
    wp_enqueue_script('custom-front-mam-js', plugin_dir_url(__FILE__) . 'assets/js/custom_front.js', array(), 'all');
    wp_enqueue_style('bootstrap-mam-css', "https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/cerulean/bootstrap.min.css");
    wp_enqueue_style('style-front-mam-css', plugin_dir_url(__FILE__) . 'assets/css/style_front.css');

    $images_str = $attr['id'];
    $images = explode(',', $images_str);
    if (count($images) > 0) {
        $result = '<div class="row">';
        foreach ($images as $image) {
            $result .= '<img class="pb-seo_lazy col-md-6" src="' . $image . '" alt="">';
        }
        $result .= '</div>';
        return $result;
    } else {
        return '';
    }
});

add_filter('pre_get_avatar_data', 'getAvatarData', 10, 2);
function getAvatarData($args, $id_or_email)
{
    $id = $id_or_email;
    if ($id instanceof WP_Comment) {
        // $url = getDefaultAvatarUrl();

        $post = get_post($id->comment_post_ID);
        if (get_post_meta($post->ID, 'comments', true)) {
            $meta_comments = get_post_meta($post->ID, 'comments', true);
            foreach ($meta_comments as $comment) {
                if ($id->comment_parent == 0) {
                    if ($id->comment_author == $comment['snippet']['topLevelComment']['snippet']['authorDisplayName']) {
                        $url = $comment['snippet']['topLevelComment']['snippet']['authorProfileImageUrl'];
                        break;
                    }
                } else {
                    if ($comment['replies']) {
                        $rep_comments = $comment['replies']['comments'];
                        foreach ($rep_comments as $rep_comment)
                            if ($id->comment_author == $rep_comment['snippet']['authorDisplayName']) {
                                $url = $rep_comment['snippet']['authorProfileImageUrl'];
                                break;
                            }
                    }
                }
            }
        }
        $args['url'] = $url;
    }
    return $args;
}
function getDefaultAvatarUrl()
{
    return 'https://lh3.googleusercontent.com/-XdUIqdMkCWA/AAAAAAAAAAI/AAAAAAAAAAA/4252rscbv5M/photo.jpg?sz=50';
}

function db_process()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'mam_campaigns';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            user_id int, 
            name varchar(255), 
            keywords varchar(255), 
            total_posts int, 
            status varchar(255), 
            last_error varchar(255), 
            create_date timestamp default current_timestamp, 
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_templates';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            name varchar(255), 
            content varchar(255), 
            create_date timestamp default current_timestamp, 
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates where name="[default]"';
    $count = $wpdb->get_var($sql);
    if ($count == 0) {
        $wpdb->insert($wpdb->prefix . 'mam_templates', array(
            'name' => '[default]',
            'content' => '[Captions]
[Youtube Video]
[Tags]
[Images]
[Related Keywords]
[RSS]
[facebook]
[twitter]
[flickr]
[pinterest]',
        ));
    }

    $table_name = $wpdb->prefix . 'mam_auth';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            user_id int, 
            api_key varchar(255), 
            bing_search_key varchar(255), 
            fb_id int, 
            tw_id int, 
            fl_id int, 
            pi_id int, 
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_facebook';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            fb_access_token varchar(255),
            fb_page_name varchar(255),  
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_twitter';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            tw_api_key varchar(255),
            tw_api_secret varchar(255),  
            tw_access_token varchar(255),  
            tw_token_secret varchar(255),  
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_pinterest';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            pi_api_key varchar(255),
            pi_keyword varchar(255),  
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_flickr';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            fl_api_key varchar(255),
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_setting';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            user_id int, 
            cron_interval int, 
            related_limit int, 
            rss_limit int, 
            facebook_limit int, 
            twitter_limit int, 
            flickr_limit int, 
            pinterest_limit int, 
            del_fl_flag int, 
            del_ll_flag int, 
            create_date timestamp default current_timestamp, 
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }

    $table_name = $wpdb->prefix . 'mam_log';
    if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name) {
        $sql = 'create table ' . $table_name . '(
            id int unsigned auto_increment, 
            user_id int, 
            log varchar(255), 
            create_date timestamp default current_timestamp, 
            primary key (id)
        )';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }
}

register_activation_hook(__FILE__, "db_process");

function mam_create()
{
    ?>
    <div class="wrap">
        <h2><?php _e('Mass Api Manager', 'mass-api-manager'); ?></h2>

        <!-- Nav pills -->
        <ul class="nav nav-pills section-tabs">
            <li class="nav-item active">
                <span class="nav-link" data-toggle="pill" href="#campaign">CAMPAIGN</span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-toggle="pill" href="#video">VIDEO</span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-toggle="pill" href="#newtemplate">TEMPLATE</span>
            </li>
            <li class="nav-item hide">
                <span class="nav-link" data-toggle="pill" href="#feeds">FEEDS</span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-toggle="pill" href="#auth">AUTH</span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-toggle="pill" href="#settings">SETTINGS</span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-toggle="pill" href="#logs">LOGS</span>
            </li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane container active" id="campaign">
                <div id="accordion_campaign">
                    <div class="card">
                        <div class="card-header">
                            <a class="card-link btn btn-info" data-toggle="collapse" href="#collapseOne">
                                Create Campaign
                            </a>
                        </div>
                        <div id="collapseOne" class="collapse show" data-parent="#accordion_campaign">
                            <div class="card-body">
                                <div class="mam_campain_progress_box progress hidden">
                                    <div class="mam_campaign_progress_bar progress-bar progress-bar-striped" style="width:0%">0%</div>
                                </div>
                                <div class="mam_campaign_spinner_box hidden">
                                    <div class="spinner-border text-muted"></div>
                                    <div class="spinner-border text-primary"></div>
                                    <div class="spinner-border text-success"></div>
                                    <div class="spinner-border text-info"></div>
                                    <div class="spinner-border text-warning"></div>
                                    <div class="spinner-border text-danger"></div>
                                    <div class="spinner-border text-secondary"></div>
                                    <div class="spinner-border text-dark"></div>
                                    <div class="spinner-border text-light"></div>
                                </div>
                                <div class="alert alert-success mam_camp_success_alert hidden">
                                </div>
                                <table class="form-table">
                                    <tbody>
                                        <tr class="campaign_name_tr">
                                            <th>Name of Campaign</th>
                                            <td><input type="text" class="regular-text" value="" id="mam_camp_name" name="campaign_name" required></td>
                                        </tr>
                                        <tr class="keyword_list_tr">
                                            <th>Keyword List</th>
                                            <td>
                                                <textarea class="code" id="mam_camp_keyword_list" cols="60" rows="5" name="keyword_list" required></textarea>
                                                <input type="file" class="hide" id="mam_keywords_file" accept=".txt" name="mam_keywords_file" />
                                                <input type="button" id="btn_keywords_import" class="btn btn-success vertical_top" value="Import">
                                                <p class="description">Please click import button to import keywords using text file.</p>
                                            </td>
                                        </tr>
                                        <tr class="secondary_keyword_tr hidden">
                                            <th>Secondary Main Keyword</th>
                                            <td><input type="text" class="regular-text" value="" id="secondary_keyword" name="secondary_keyword"></td>
                                        </tr>
                                        <tr class="category_tr">
                                            <th>Category to Post</th>
                                            <td>
                                                <select id="mam_post_category" name="category">
                                                    <option>Uncategorized</option>
                                                    <?php
                                                        $categories = get_categories();
                                                        foreach ($categories as $category)
                                                            if ($category->name != 'Uncategorized')
                                                                echo '<option>' . $category->name . '</option>';
                                                        ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="select_template_tr">
                                            <th>Select Template</th>
                                            <td>
                                                <select id="selected_template_name">
                                                    <?php
                                                        global $wpdb;
                                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_templates';
                                                        $templates = $wpdb->get_results($sql);
                                                        foreach ($templates as $template) {
                                                            ?>
                                                        <option><?php echo $template->name ?></option>
                                                    <?php
                                                        }
                                                        ?>
                                                </select>
                                            </td>
                                        <tr>
                                            <td>
                                                <input type="button" id="btn_mam_create_camp_cron" class="btn btn-success" value="Create Campaign Using Cron">
                                            </td>
                                            <td>
                                                <input type="button" id="btn_mam_create_camp" class="btn btn-warning" value="Create Campaign">
                                            </td>
                                        </tr>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapseTwo">
                                List of Campaign
                            </a>
                        </div>
                        <div id="collapseTwo" class="collapse" data-parent="#accordion_campaign">
                            <div class="card-body">
                                <table class="form-table">
                                    <thead>
                                        <tr class="keyword_list_tr">
                                            <th>Campaign Name</th>
                                            <th>Date of Creation</th>
                                            <th>Last Work</th>
                                            <th>Status</th>
                                            <th>Keywords</th>
                                            <th>Total Posts</th>
                                            <th>Last error</th>
                                        </tr>
                                    </thead>
                                    <tbody id="camp_table_body">
                                        <?php
                                            global $wpdb;
                                            $sql = 'select * from ' . $wpdb->prefix . 'mam_campaigns where user_id=' . get_current_user_id() . ' order by id desc';
                                            $campaigns = $wpdb->get_results($sql);
                                            foreach ($campaigns as $campaign) {
                                                ?>
                                            <tr>
                                                <td><?php echo $campaign->name ?></td>
                                                <td><?php echo $campaign->create_date ?></td>
                                                <td><?php echo $campaign->create_date ?></td>
                                                <td><?php echo $campaign->status ?></td>
                                                <td><?php echo $campaign->keywords ?></td>
                                                <td><?php echo $campaign->total_posts ?></td>
                                                <td><?php echo $campaign->last_error ?></td>
                                            </tr>
                                        <?php
                                            }
                                            ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane container" id="video">
                <div class="card">
                    <div id="collapseOne" class="collapse show" data-parent="#accordion_campaign">
                        <div class="card-body">
                            <div class="mam_video_spinner_box hidden" style="text-align: center;">
                                <div class="spinner-border text-primary"></div>
                                <div class="spinner-border text-primary"></div>
                                <div class="spinner-border text-primary"></div>
                            </div>
                            <div class="alert alert-success mam_video_success_alert hidden">
                            </div>
                            <table class="form-table">
                                <tbody>
                                    <tr class="campaign_name_tr">
                                        <th>Video Url</th>
                                        <td><input type="text" class="regular-text" value="" id="mam_video_url" name="mam_video_url" required></td>
                                    </tr>
                                    <tr class="keyword_list_tr">
                                        <th>Keyword</th>
                                        <td>
                                            <input type="text" class="regular-text" value="" id="mam_video_keyword" name="mam_video_keyword" required>
                                            <p class="description">Used for getting content for RSS, Flickr, Bing Images, Twitter...</p>
                                        </td>
                                    </tr>
                                    <tr class="category_tr">
                                        <th>Category to Post</th>
                                        <td>
                                            <select id="mam_video_post_category" name="category">
                                                <option>Uncategorized</option>
                                                <?php
                                                    $categories = get_categories();
                                                    foreach ($categories as $category)
                                                        if ($category->name != 'Uncategorized')
                                                            echo '<option>' . $category->name . '</option>';
                                                    ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="select_template_tr">
                                        <th>Select Template</th>
                                        <td>
                                            <select id="selected_video_template_name">
                                                <?php
                                                    global $wpdb;
                                                    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates';
                                                    $templates = $wpdb->get_results($sql);
                                                    foreach ($templates as $template) {
                                                        ?>
                                                    <option><?php echo $template->name ?></option>
                                                <?php
                                                    }
                                                    ?>
                                            </select>
                                        </td>
                                    <tr>
                                        <td>
                                            <input type="button" id="btn_mam_create_video" class="btn btn-warning" value="Create Post">
                                        </td>
                                    </tr>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane container fade" id="newtemplate">
                <div id="accordion_template">
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapseTwo_temp">
                                List of Template
                            </a>
                        </div>
                        <div id="collapseTwo_temp" class="collapse show" data-parent="#accordion_template">
                            <div class="card-body">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="template_list_tr">
                                            <th>Template Name</th>
                                            <th>Template Content</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="mam_template_list list-group">
                                                    <?php
                                                        global $wpdb;
                                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_templates';
                                                        $templates = $wpdb->get_results($sql);
                                                        $i = 0;
                                                        foreach ($templates as $template) {
                                                            ?>
                                                        <a class="mam_template_list_item list-group-item list-group-item-action <?php if ($i == 0) echo 'active';
                                                                                                                                        $i++; ?>">
                                                            <?php echo $template->name ?>
                                                        </a>
                                                    <?php
                                                        }
                                                        ?>
                                                </div>
                                            </td>
                                            <td>
                                                <textarea class="mam_template_list_content" width="100%"><?php echo $templates[0]->content ?></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="card-link btn btn-info" data-toggle="collapse" href="#collapseOne_temp">
                                Create Template
                            </a>
                        </div>
                        <div id="collapseOne_temp" class="collapse" data-parent="#accordion_template">
                            <div class="card-body">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="campaign_name_tr">
                                            <th>Template Title</th>
                                            <td><input type="text" class="regular-text" value="" id="mam_template_name" name="campaign_name"></td>
                                        </tr>
                                        <tr class="keyword_list_tr">
                                            <th>Content</th>
                                            <td>
                                                <textarea class="code" id="mam_template_content" cols="60" rows="5" name="keyword_list"></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <input type="button" id="btn_mam_create_template" class="btn btn-success" value="Create Template">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="tab-pane container fade" id="feeds">
                <div class="section-sources" id="sources-list" data-view-mode="sources-list">
                    <div class="section" id="feeds-list-section">
                        <h1 class="desc-following">
                            <span>List of feeds</span>
                            <span class="admin-button btn btn-success button-add" data-toggle="modal" data-target="#feedsModal">Create feed</span>
                        </h1>
                        <p class="desc">Each feed can be connected to multiple streams. Cache for feed is being built immediately on creation. You can disable any feed and it will be disabled in all streams where it's connected. Feeds with errors are automatically disabled. <a class="ff-pseudo-link" href="#">Show only error feeds</a>.</p>
                        <div id="feeds-view">
                            <table class="feeds-list">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Feed</th>
                                        <th></th>
                                        <th>Settings</th>
                                        <th>Last update</th>
                                        <th>Live</th>
                                    </tr>
                                </thead>
                                <tbody id="feeds-list">
                                    <tr data-uid="cv99734" data-network="youtube" class="feed-enabled animated yes">
                                        <td class="controls"><i class="flaticon-tool_more"></i>
                                            <ul class="feed-dropdown-menu">
                                                <li data-action="filter">Filter feed</li>
                                                <li data-action="cache">Rebuild cache</li>
                                            </ul><i class="flaticon-tool_edit"></i> <i class="flaticon-copy"></i> <i class="flaticon-tool_delete"></i>
                                        </td>
                                        <td class="td-feed"><i class="flaticon-youtube"></i></td>
                                        <td class="td-status"><span class="cache-status-ok"></span></td>
                                        <td class="td-info"><span><span class="highlight">pcos ayuverda</span></span><span><span class="highlight">search</span></span><span><span class="highlight highlight-id">ID: cv99734</span></span></td>
                                        <td class="td-last-update">Jul 28 22:16 (Every hour)</td>
                                        <td class="td-enabled"><label for="feed-enabled-cv99734"><input checked="" id="feed-enabled-cv99734" class="switcher" type="checkbox" name="feed-enabled-cv99734" value="yep">
                                                <div>
                                                    <div></div>
                                                </div>
                                            </label></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="holder"><a class="jp-previous jp-disabled">←</a><a class="jp-current">1</a><span class="jp-hidden">...</span><a class="jp-next jp-disabled">→</a></div>
                            <div class="popup">
                                <div class="section">
                                    <i class="popupclose flaticon-close-4"></i>
                                    <div class="networks-choice add-feed-step">
                                        <h1>Create new feed</h1>
                                        <ul class="networks-list">
                                            <li class="network-twitter" data-network="twitter" data-network-name="Twitter">
                                                <i class="flaticon-twitter"></i>
                                            </li>
                                            <li class="network-facebook" data-network="facebook" data-network-name="Facebook">
                                                <i class="flaticon-facebook"></i>
                                            </li>
                                            <li class="network-instagram" data-network="instagram" data-network-name="Instagram">
                                                <i class="flaticon-instagram"></i>
                                            </li>
                                            <li class="network-youtube" data-network="youtube" data-network-name="YouTube">
                                                <i class="flaticon-youtube"></i>
                                            </li>
                                            <li class="network-pinterest" data-network="pinterest" data-network-name="Pinterest">
                                                <i class="flaticon-pinterest"></i>
                                            </li>
                                            <li class="network-linkedin" data-network="linkedin" data-network-name="LinkedIn">
                                                <i class="flaticon-linkedin"></i>
                                            </li>

                                            <li class="network-flickr" data-network="flickr" data-network-name="Flickr">
                                                <i class="flaticon-flickr"></i>
                                            </li>
                                            <li class="network-tumblr" data-network="tumblr" data-network-name="Tumblr" style="margin-right:0">
                                                <i class="flaticon-tumblr"></i>
                                            </li>
                                            <br>

                                            <li class="network-google" data-network="google" data-network-name="Google +">
                                                <i class="flaticon-google"></i>
                                            </li>
                                            <li class="network-vimeo" data-network="vimeo" data-network-name="Vimeo">
                                                <i class="flaticon-vimeo"></i>
                                            </li>
                                            <li class="network-wordpress" data-network="wordpress" data-network-name="WordPress">
                                                <i class="flaticon-wordpress"></i>
                                            </li>
                                            <li class="network-foursquare" data-network="foursquare" data-network-name="Foursquare">
                                                <i class="flaticon-foursquare"></i>
                                            </li>
                                            <li class="network-soundcloud" data-network="soundcloud" data-network-name="SoundCloud">
                                                <i class="flaticon-soundcloud"></i>
                                            </li>
                                            <li class="network-dribbble" data-network="dribbble" data-network-name="Dribbble">
                                                <i class="flaticon-dribbble"></i>
                                            </li>
                                            <li class="network-rss" data-network="rss" data-network-name="RSS" style="margin-right:0">
                                                <i class="flaticon-rss"></i>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="networks-content  add-feed-step">
                                        <div id="feed-views">
                                            <div class="feed-view" data-feed-type="youtube" data-uid="cv99734">
                                                <h1>YouTube feed settings</h1>
                                                <dl class="section-settings">
                                                    <dt>FEED TYPE</dt>
                                                    <dd> <input id="cv99734-user-timeline-type" type="radio" name="cv99734-timeline-type" value="user_timeline" checked=""> <label for="cv99734-user-timeline-type">User feed</label><br><br> <input id="cv99734-channel-type" type="radio" name="cv99734-timeline-type" value="channel"> <label for="cv99734-channel-type">Channel</label><br><br> <input id="cv99734-pl-type" type="radio" name="cv99734-timeline-type" value="playlist"> <label for="cv99734-pl-type">Playlist</label><br><br> <input id="cv99734-search-timeline-type" type="radio" name="cv99734-timeline-type" value="search" checked="checked"> <label for="cv99734-search-timeline-type">Search</label> </dd>
                                                    <dt class=""> Content to show <div class="desc hint-block"> <span class="hint-link"> <img src=""> </span>
                                                            <div class="hint hint-pro">
                                                                <h1>Content to show</h1>
                                                                <ul>
                                                                    <li><b>User feed</b> — enter YouTube username with public access.</li>
                                                                    <li><b>Channel</b> — enter channel ID. <a href="https://support.google.com/youtube/answer/3250431?hl=en" target="_blank">What is it?</a></li>
                                                                    <li><b>Playlist</b> — enter playlist ID. <a href="http://docs.social-streams.com/article/139-find-youtube-playlist-id" target="_blank">What is it?</a></li>
                                                                    <li><b>Search</b> — enter any search query.</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </dt>
                                                    <dd class=""><input type="text" name="cv99734-content"></dd>
                                                    <dt>Playlist reverse order</dt>
                                                    <dd> <label for="cv99734-playlist-order"> <input id="cv99734-playlist-order" class="switcher" type="checkbox" name="cv99734-playlist-order" value="yep">
                                                            <div>
                                                                <div></div>
                                                            </div>
                                                        </label> </dd>
                                                    <dt>Feed updates frequency</dt>
                                                    <dd>
                                                        <div class="select-wrapper"> <select name="cv99734-cache_lifetime" id="cv99734-cache_lifetime">
                                                                <option value="60">Every hour</option>
                                                                <option value="360">Every 6 hours</option>
                                                                <option value="1440">Once a day</option>
                                                                <option value="10080">Once a week</option>
                                                            </select> </div>
                                                    </dd>
                                                    <dt>Posts to load during update<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>
                                                    </dt>
                                                    <dd>
                                                        <div class="select-wrapper"> <select name="cv99734-posts" id="cv99734-post">
                                                                <option value="1">1 post</option>
                                                                <option value="5">5 posts</option>
                                                                <option selected="" value="10">10 posts</option>
                                                                <option value="20">20 posts</option>
                                                            </select></div>
                                                    </dd>
                                                    <dt> MODERATE THIS FEED <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>
                                                    </dt>
                                                    <dd><label for="cv99734-mod"><input id="cv99734-mod" class="switcher" type="checkbox" name="cv99734-mod" value="yep">
                                                            <div>
                                                                <div></div>
                                                            </div>
                                                        </label></dd>
                                                </dl><input type="hidden" id="cv99734-enabled" value="yep" checked="" name="cv99734-enabled">
                                            </div>
                                        </div>
                                        <div id="filter-views">
                                            <div class="feed-view filter-feed" data-filter-uid="cv99734">
                                                <h1>Filter Feed Content</h1>
                                                <dl class="section-settings">
                                                    <dt class="">Exclude all</dt>
                                                    <dd class=""> <input type="hidden" data-type="filter-exclude-holder" name="cv99734-filter-by-words" value=""> <input type="text" data-action="add-filter" data-id="cv99734" data-type="exclude" placeholder="Type and hit Enter">
                                                        <ul class="filter-labels" data-type="exclude"></ul>
                                                    </dd>
                                                </dl>
                                                <dl class="section-settings">
                                                    <dt class="">Include all</dt>
                                                    <dd class=""> <input type="hidden" data-type="filter-include-holder" name="cv99734-include" value=""> <input type="text" data-action="add-filter" data-id="cv99734" data-type="include" placeholder="Type and hit Enter">
                                                        <ul class="filter-labels" data-type="include"></ul>
                                                    </dd>
                                                </dl>
                                                <div class="hint-block"> <a class="hint-link" href="#" data-action="hint-toggle">How to Filter</a>
                                                    <div class="hint">
                                                        <h1>Hints on Filtering</h1>
                                                        <div class="desc">
                                                            <p> 1. <strong>Filter by word</strong> — type any word<br> </p>
                                                            <p> 2. <strong>Filter by URL</strong> — enter any substring with hash like this #badpost or #1234512345<br> </p>
                                                            <p> 3. <strong>Filter by account</strong> — type word with @ symbol e.g. @apple<br> </p> <br>
                                                            <p> <a target="_blank" title="Learn more" href="http://docs.social-streams.com/article/71-automatic-moderation-with-filters">Learn more</a> </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="feed-popup-controls add">
                                            <span id="feed-sbmt-1" class="admin-button green-button submit-button">Add feed</span>
                                            <span class="space"></span><span class="admin-button grey-button button-go-back">Back to first step</span>
                                        </p>
                                        <p class="feed-popup-controls edit">
                                            <span id="feed-sbmt-2" class="admin-button green-button submit-button">Save changes</span>
                                        </p>
                                        <p class="feed-popup-controls enable">
                                            <span id="feed-sbmt-3" class="admin-button blue-button submit-button">Save &amp; Enable</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane container fade" id="auth">
                <div id="accordion_auth">
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse1_auth">
                                Google+ and YouTube auth settings
                            </a>
                        </div>
                        <div id="collapse1_auth" class="collapse show" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <tbody>
                                        <?php
                                            global $wpdb;
                                            $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
                                            $auth = $wpdb->get_row($sql);
                                            ?>

                                        <tr class="get_instagram_tr">
                                            <th>API Key</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $auth->api_key ?>" id="mam_gy_key" name="campaign_name" required></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_gykey" value="save"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse6_auth">
                                Related Keywords
                            </a>
                        </div>
                        <div id="collapse6_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="bing_azure_api_tr">
                                            <th> Bing Image Search API Key</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $auth->bing_search_key ?>" id="mam_bs_key" name="mam_bs_key"></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_bskey" value="save"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse3_auth">
                                Facebook auth settings
                            </a>
                        </div>
                        <div id="collapse3_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <?php
                                        global $wpdb;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
                                        $auth = $wpdb->get_row($sql);
                                        $fb_id = $auth->fb_id;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_facebook where id=' . $fb_id;
                                        $fb = $wpdb->get_row($sql);
                                        ?>
                                    <tbody>
                                        <tr class="use_own_app_tr hidden">
                                            <th>Use Own App</th>
                                            <td><input type="text" class="regular-text" value="" id="campaign_name" name="campaign_name"></td>
                                        </tr>
                                        <tr class="access_tokens_tr">
                                            <th>Access Token</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $fb->fb_access_token ?>" id="fb_access_token" name="fb_access_token"></td>
                                        </tr>
                                        <tr class="page_name">
                                            <th>Page Name</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $fb->fb_page_name ?>" id="fb_page_name" name="fb_page_name"></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_fbtoken" value="save"></td>
                                        </tr>
                                        <tr class="app_id hidden">
                                            <th>App Id</th>
                                            <td><input type="text" class="regular-text" value="" id="campaign_name" name="campaign_name"></td>
                                        </tr>
                                        <tr class="app_secret_tr hidden">
                                            <th>App Secret</th>
                                            <td><input type="text" class="regular-text" value="" id="campaign_name" name="campaign_name"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="card-link btn btn-info" data-toggle="collapse" href="#collapse2_auth">
                                Twitter auth settings
                            </a>
                        </div>
                        <div id="collapse2_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <?php
                                        global $wpdb;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
                                        $auth = $wpdb->get_row($sql);
                                        $tw_id = $auth->tw_id;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_twitter where id=' . $tw_id;
                                        $tw = $wpdb->get_row($sql);
                                        ?>
                                    <tbody>
                                        <tr class="consumer_key_tr">
                                            <th>Consumer Key (Api Key)</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $tw->tw_api_key ?>" id="tw_api_key" name="tw_api_key"></td>
                                        </tr>
                                        <tr class="consumer_secret_tr">
                                            <th>Consumer Secret (Api Secret)</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $tw->tw_api_secret ?>" id="tw_api_secret" name="tw_api_secret"></td>
                                        </tr>
                                        <tr class="access_token_tr">
                                            <th>Access Token</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $tw->tw_access_token ?>" id="tw_access_token" name="tw_access_token"></td>
                                        </tr>
                                        <tr class="access_token_secret_tr">
                                            <th>Access Token Secret</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $tw->tw_token_secret ?>" id="tw_token_secret" name="tw_token_secret"></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_twtoken" value="save"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse4_auth">
                                Flickr auth settings
                            </a>
                        </div>
                        <div id="collapse4_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <?php
                                        global $wpdb;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
                                        $auth = $wpdb->get_row($sql);
                                        $fl_id = $auth->fl_id;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_flickr where id=' . $fl_id;
                                        $fl = $wpdb->get_row($sql);
                                        ?>
                                    <tbody>
                                        <tr class="get_flickr_tr">
                                            <th>API Key</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $fl->fl_api_key ?>" id="fl_api_key" name="fl_api_key"></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_fltoken" value="save"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse7_auth">
                                Pinterest auth settings
                            </a>
                        </div>
                        <div id="collapse7_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <?php
                                        global $wpdb;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
                                        $auth = $wpdb->get_row($sql);
                                        $pi_id = $auth->pi_id;
                                        $sql = 'select * from ' . $wpdb->prefix . 'mam_pinterest where id=' . $pi_id;
                                        $pi = $wpdb->get_row($sql);
                                        ?>
                                    <tbody>
                                        <tr class="api_key_tr">
                                            <th>API Key</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $pi->pi_api_key ?>" id="pi_api_key" name="pi_api_key"></td>
                                        </tr>
                                        <tr class="pin_keyword">
                                            <th>Pinterest Keyword</th>
                                            <td><input type="text" class="regular-text" value="<?php echo $pi->pi_keyword ?>" id="pi_keyword" name="pi_keyword"></td>
                                            <td><input type="button" class="btn btn-primary" id="btn_mam_save_pitoken" value="save"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card hidden">
                        <div class="card-header">
                            <a class="collapsed card-link btn btn-info" data-toggle="collapse" href="#collapse8_auth">
                                Reddit
                            </a>
                        </div>
                        <div id="collapse8_auth" class="collapse" data-parent="#accordion_auth">
                            <div class="card-body">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="get_reddit_tr">
                                            <th>Get related reddit Content</th>
                                            <td><input type="text" class="regular-text" value="" id="campaign_name" name="campaign_name"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane container fade" id="settings">
                <div class="section" id="general-settings">
                    <h1 class="desc-following">General Settings</h1>
                    <p class="desc">Adjust plugin's global settings here.</p>
                    <dl class="section-settings">
                        <?php
                            global $wpdb;
                            $sql = 'select * from ' . $wpdb->prefix . 'mam_setting where user_id=' . get_current_user_id();
                            $setting = $wpdb->get_row($sql);
                            ?>
                        <dt class="ff_mod_roles ff_hide4site">
                            Interval of Cron Job
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="cron_interval" class="short" value="<?php echo $setting->cron_interval; ?>">
                            <label for="">min</label>
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">
                            Delete First Line from Caption/subtitle
                        </dt>
                        <dd>
                            <div class="checkbox-row"><input type="checkbox" value="yep" name="" id="mam_delete_firstline" <?php if ($setting->del_fl_flag == 1) echo "checked"; ?>>
                                <label for="">first line</label>
                            </div>
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">
                            Delete Last Line from Caption/subtitle
                        </dt>
                        <dd>
                            <div class="checkbox-row"><input type="checkbox" value="yep" name="" id="mam_delete_lastline" <?php if ($setting->del_ll_flag == 1) echo "checked"; ?>>
                                <label for="">last line</label>
                            </div>
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit of Related Keywords
                            <p class="desc">Count of Related Keywords.</p>
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="related_limit" class="short" value="<?php echo $setting->related_limit; ?>">
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit Number of RSS Posts
                            <p class="desc">In the RSS feeds.</p>
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="rss_limit" class="short" value="<?php echo $setting->rss_limit; ?>">
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit Number of Facebook Posts
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="facebook_limit" class="short" value="<?php echo $setting->facebook_limit; ?>">
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit Number of Twitter Posts
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="twitter_limit" class="short" value="<?php echo $setting->twitter_limit; ?>">
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit Number of Flickr Posts
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="flickr_limit" class="short" value="<?php echo $setting->flickr_limit; ?>">
                        </dd>
                        <dt class="ff_mod_roles ff_hide4site">Limit Number of Pinterest Posts
                        </dt>
                        <dd class="ff_mod_roles ff_hide4site">
                            <input type="number" id="pinterest_limit" class="short" value="<?php echo $setting->pinterest_limit; ?>">
                        </dd>
                    </dl>
                    <span id="btn_mam_save_setting" class="admin-button btn btn-success submit-button">Save Changes</span>
                </div>
            </div>
            <div class="tab-pane container fade" id="logs">
                <table class="form-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Log</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="log_table_body">
                        <?php
                            global $wpdb;
                            $sql = 'select * from ' . $wpdb->prefix . 'mam_log where user_id=' . get_current_user_id() . ' order by id desc';
                            $logs = $wpdb->get_results($sql);
                            foreach ($logs as $log) {
                                ?>
                            <tr>
                                <td><?php echo $log->id ?></td>
                                <td><?php echo $log->log ?></td>
                                <td><?php echo $log->create_date ?></td>
                            </tr>
                        <?php
                            }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- The Modal -->
        <div class="modal" id="feedsModal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Networks List</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <!-- Modal body -->
                    <div class="modal-body">
                        <ul class="networks-list">
                            <li class="network-twitter" data-network="twitter" data-network-name="Twitter">
                                <i class="flaticon-twitter"></i>
                            </li>
                            <li class="network-facebook" data-network="facebook" data-network-name="Facebook">
                                <i class="flaticon-facebook"></i>
                            </li>
                            <li class="network-instagram" data-network="instagram" data-network-name="Instagram">
                                <i class="flaticon-instagram"></i>
                            </li>
                            <li class="network-youtube" data-network="youtube" data-network-name="YouTube">
                                <i class="flaticon-youtube"></i>
                            </li>
                            <li class="network-pinterest" data-network="pinterest" data-network-name="Pinterest">
                                <i class="flaticon-pinterest"></i>
                            </li>
                            <li class="network-google" data-network="google" data-network-name="Google +">
                                <i class="flaticon-google"></i>
                            </li>
                            <li class="network-rss" data-network="rss" data-network-name="RSS">
                                <i class="flaticon-rss"></i>
                            </li>
                            <li class="network-reddit" data-network="reddit" data-network-name="Reddit" style="margin-right:0">
                                <i class="flaticon-star-o"></i>
                            </li>
                        </ul>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php
}
