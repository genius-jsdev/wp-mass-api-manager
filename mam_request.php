<?php
$path = '../../../';

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

include_once './Library/MamLogger.php';
require_once './Library/twitteroauth/autoload.php';


use Abraham\TwitterOAuth\TwitterOAuth;

$logger = MamLogger::get_instance();

global $wpdb;

$kind = $_POST['kind'];
if ($kind == 'send_log') {
    $log = $_POST['log'];
    $logger->send_log($log);
} else if ($kind == 'save_gy_key') {
    $flag = 1;
    $key = $_POST['key'];
    $yt_url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&key=' . $key;
    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );
    try {
        $data_json = file_get_contents($yt_url);
    } catch (Exception $e) {
        $flag = 0;
    }
    restore_error_handler();
    if ($flag) {
        $user_id = get_current_user_id();
        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
        $count = $wpdb->get_var($sql);

        if ($count == 0) {
            $wpdb->insert($wpdb->prefix . 'mam_auth', array(
                'user_id' => get_current_user_id(),
                'api_key' => $key
            ));
        } else {
            $wpdb->update($wpdb->prefix . 'mam_auth', array(
                'api_key' => $key
            ), array('user_id' => $user_id));
        }
    }
    echo $flag;
} else if ($kind == 'save_bs_key') {
    $key = $_POST['bs_key'];
    $user_id = get_current_user_id();
    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
    $count = $wpdb->get_var($sql);

    if ($count == 0) {
        $wpdb->insert($wpdb->prefix . 'mam_auth', array(
            'user_id' => $user_id,
            'bing_search_key' => $key
        ));
    } else {
        $wpdb->update($wpdb->prefix . 'mam_auth', array(
            'bing_search_key' => $key
        ), array('user_id' => $user_id));
    }
} else if ($kind == 'save_fb_token') {
    $token = $_POST['fb_access_token'];
    $page_name = $_POST['fb_page_name'];

    $user_id = get_current_user_id();
    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
    $count = $wpdb->get_var($sql);
    if ($count > 0) {
        $row = $wpdb->get_row($sql);
        $fb_id = $row->fb_id;

        if ($fb_id == null) {
            $wpdb->insert($wpdb->prefix . 'mam_facebook', array(
                'fb_access_token' => $token,
                'fb_page_name' => $page_name
            ));
            $fid = $wpdb->insert_id;
            $wpdb->update($wpdb->prefix . 'mam_auth', array(
                'fb_id' => $fid
            ), array('user_id' => $user_id));
        } else {
            $wpdb->update($wpdb->prefix . 'mam_facebook', array(
                'fb_access_token' => $token,
                'fb_page_name' => $page_name
            ), array('id' => $fb_id));
        }
    } else {
        $wpdb->insert($wpdb->prefix . 'mam_facebook', array(
            'fb_access_token' => $token,
            'fb_page_name' => $page_name
        ));
        $fid = $wpdb->insert_id;
        $wpdb->insert($wpdb->prefix . 'mam_auth', array(
            'user_id' => $user_id,
            'fb_id' => $fid
        ));
    }
} else if ($kind == 'save_pi_token') {
    $key = $_POST['pi_api_key'];
    $keyword = $_POST['pi_keyword'];

    $user_id = get_current_user_id();
    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
    $count = $wpdb->get_var($sql);
    if ($count > 0) {
        $row = $wpdb->get_row($sql);
        $pi_id = $row->pi_id;

        if ($pi_id == null) {
            $wpdb->insert($wpdb->prefix . 'mam_pinterest', array(
                'pi_api_key' => $key,
                'pi_keyword' => $keyword
            ));
            $pid = $wpdb->insert_id;
            $wpdb->update($wpdb->prefix . 'mam_auth', array(
                'pi_id' => $pid
            ), array('user_id' => $user_id));
        } else {
            $wpdb->update($wpdb->prefix . 'mam_pinterest', array(
                'pi_api_key' => $key,
                'pi_keyword' => $keyword
            ), array('id' => $pi_id));
        }
    } else {
        $wpdb->insert($wpdb->prefix . 'mam_pinterest', array(
            'pi_api_key' => $key,
            'pi_keyword' => $keyword
        ));
        $pid = $wpdb->insert_id;
        $wpdb->insert($wpdb->prefix . 'mam_auth', array(
            'user_id' => $user_id,
            'pi_id' => $pid
        ));
    }
} else if ($kind == 'save_tw_token') {
    $flag = 1;
    $tw_api_key = $_POST['tw_api_key'];
    $tw_api_secret = $_POST['tw_api_secret'];
    $tw_access_token = $_POST['tw_access_token'];
    $tw_token_secret = $_POST['tw_token_secret'];

    $connection = new TwitterOAuth($tw_api_key, $tw_api_secret, $tw_access_token, $tw_token_secret);
    $content = $connection->get("account/verify_credentials");

    if (isset($content->errors)) {
        $flag = 0;
    }

    if ($flag) {
        $user_id = get_current_user_id();
        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
        $count = $wpdb->get_var($sql);
        if ($count > 0) {
            $row = $wpdb->get_row($sql);
            $tw_id = $row->tw_id;

            if ($tw_id == null) {
                $wpdb->insert($wpdb->prefix . 'mam_twitter', array(
                    'tw_api_key' => $tw_api_key,
                    'tw_api_secret' => $tw_api_secret,
                    'tw_access_token' => $tw_access_token,
                    'tw_token_secret' => $tw_token_secret
                ));
                $tid = $wpdb->insert_id;
                $wpdb->update($wpdb->prefix . 'mam_auth', array(
                    'tw_id' => $tid
                ), array('user_id' => $user_id));
            } else {
                $wpdb->update($wpdb->prefix . 'mam_twitter', array(
                    'tw_api_key' => $tw_api_key,
                    'tw_api_secret' => $tw_api_secret,
                    'tw_access_token' => $tw_access_token,
                    'tw_token_secret' => $tw_token_secret
                ), array('id' => $tw_id));
            }
        } else {
            $wpdb->insert($wpdb->prefix . 'mam_twitter', array(
                'tw_api_key' => $tw_api_key,
                'tw_api_secret' => $tw_api_secret,
                'tw_access_token' => $tw_access_token,
                'tw_token_secret' => $tw_token_secret
            ));
            $tid = $wpdb->insert_id;
            $wpdb->insert($wpdb->prefix . 'mam_auth', array(
                'user_id' => $user_id,
                'tw_id' => $tid
            ));
        }
    }
    echo $flag;
} else if ($kind == 'save_fl_token') {
    $fl_api_key = $_POST['fl_api_key'];

    $keyword = 'laravel';
    $base_url = 'https://api.flickr.com/services/rest/?method=';

    $photo_array = getArrayFromXML($base_url . 'flickr.photos.search&per_page=3&api_key=' . $fl_api_key . '&text=' . $keyword);

    if ($photo_array['@attributes']['stat'] != "ok") {
        echo $photo_array['err']['@attributes']['msg'];
    } else {
        $user_id = get_current_user_id();
        $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id="' . $user_id . '"';
        $count = $wpdb->get_var($sql);
        if ($count > 0) {
            $row = $wpdb->get_row($sql);
            $fl_id = $row->fl_id;

            if (!($fl_id > 0)) {
                $wpdb->insert($wpdb->prefix . 'mam_flickr', array(
                    'fl_api_key' => $fl_api_key,
                ));
                $flid = $wpdb->insert_id;
                $wpdb->update($wpdb->prefix . 'mam_auth', array(
                    'fl_id' => $flid
                ), array('user_id' => $user_id));
            } else {
                $wpdb->update($wpdb->prefix . 'mam_flickr', array(
                    'fl_api_key' => $fl_api_key,
                ), array('id' => $fl_id));
            }
        } else {
            $wpdb->insert($wpdb->prefix . 'mam_flickr', array(
                'fl_api_key' => $fl_api_key,
            ));
            $flid = $wpdb->insert_id;
            $wpdb->insert($wpdb->prefix . 'mam_auth', array(
                'user_id' => $user_id,
                'fl_id' => $flid
            ));
        }
    }
} else if ($kind == 'create_camp') {
    $camp_name = $_POST['camp_name'];
    $total_posts = $_POST['total_posts'];
    $count = $_POST['count'];
    $keywords = $total_posts . '/' . $count;
    $status = 'Works';
    $last_error = '';

    if (isset($_POST['flag'])) {
        // if ($_POST['flag'] == 0)
        // $logger->send_log('Post "' . $_POST['keyword'] . '" is not created.');
    }

    $wpdb->insert($wpdb->prefix . 'mam_campaigns', array(
        'user_id' => get_current_user_id(),
        'name' => $camp_name,
        'keywords' => $keywords,
        'total_posts' => $total_posts,
        'status' => $status,
        'last_error' => $last_error
    ));

    $sql = 'select * from ' . $wpdb->prefix . 'mam_campaigns where user_id="' . get_current_user_id() . '"';
    $campaigns = $wpdb->get_results($sql);
    $sql1 = 'select * from ' . $wpdb->prefix . 'mam_log where user_id="' . get_current_user_id() . '" order by id desc';
    $logs = $wpdb->get_results($sql1);
    echo json_encode(array('campaigns' => $campaigns, 'logs' => $logs));
} else if ($kind == 'show_temp') {
    $temp_name = trim($_POST['temp_name']);
    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates where name="' . $temp_name . '"';
    $template = $wpdb->get_row($sql);
    echo $template->content;
} else if ($kind == 'save_setting') {
    $cron_interval = $_POST['cron_interval'];
    $related_limit = $_POST['related_limit'];
    $rss_limit = $_POST['rss_limit'];
    $facebook_limit = $_POST['facebook_limit'];
    $twitter_limit = $_POST['twitter_limit'];
    $flickr_limit = $_POST['flickr_limit'];
    $pinterest_limit = $_POST['pinterest_limit'];
    $del_fl_flag = $_POST['del_fl_flag'];
    $del_ll_flag = $_POST['del_ll_flag'];
    $user_id = get_current_user_id();
    $sql = 'select * from ' . $wpdb->prefix . 'mam_setting where user_id="' . $user_id . '"';
    $count = $wpdb->get_var($sql);

    if ($count == 0) {
        $wpdb->insert($wpdb->prefix . 'mam_setting', array(
            'user_id' => $user_id,
            'cron_interval' => $cron_interval,
            'related_limit' => $related_limit,
            'rss_limit' => $rss_limit,
            'facebook_limit' => $facebook_limit,
            'twitter_limit' => $twitter_limit,
            'flickr_limit' => $flickr_limit,
            'pinterest_limit' => $pinterest_limit,
            'del_fl_flag' => $del_fl_flag,
            'del_ll_flag' => $del_ll_flag,
        ));
    } else {
        $wpdb->update($wpdb->prefix . 'mam_setting', array(
            'cron_interval' => $cron_interval,
            'related_limit' => $related_limit,
            'rss_limit' => $rss_limit,
            'facebook_limit' => $facebook_limit,
            'twitter_limit' => $twitter_limit,
            'flickr_limit' => $flickr_limit,
            'pinterest_limit' => $pinterest_limit,
            'del_fl_flag' => $del_fl_flag,
            'del_ll_flag' => $del_ll_flag,
        ), array('user_id' => $user_id));
    }
} else if ($kind == 'create_temp') {
    $temp_name = $_POST['temp_name'];
    $temp_content = $_POST['temp_content'];
    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates where name="' . $temp_name . '"';
    $count = $wpdb->get_var($sql);
    if ($count == 0) {
        $wpdb->insert($wpdb->prefix . 'mam_templates', array(
            'name' => $temp_name,
            'content' => $temp_content
        ));
    }
    echo $temp_name;
} else if ($kind == 'create_post') {
    $title = $_POST['title'];
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : $title;
    $content = $_POST['content'];
    $status = $_POST['status'];
    $tags = $_POST['tags'];
    $category = $_POST['category'];
    $comments = $_POST['comments'];
    $thumbnail = $_POST['thumbnail'];
    $rss_limit = $_POST['rss_limit'];
    $twitter_limit = $_POST['twitter_limit'];
    $flickr_limit = $_POST['flickr_limit'];
    $pinterest_limit = $_POST['pinterest_limit'];
    $tw_info = $_POST['tw_info'];
    $pi_info = $_POST['pi_info'];
    $fl_api_key = $_POST['fl_api_key'];

    $tw_result = get_twitter_fetch($keyword, $tw_info, $twitter_limit, $logger);
    $rss_result = get_rss_fetch($title, $rss_limit, $logger);
    $pi_result = get_pinterest_fetch($pi_info, $pinterest_limit, $logger);
    $fl_result = get_flickr_fetch($keyword, $fl_api_key, $flickr_limit, $logger);

    $content = str_replace('[RSS]', $rss_result, $content);
    $post = array(
        'post_type' => 'post',
        'post_status' => $status,
    );
    $post['post_content'] = $content;
    $post['post_title'] = $title;

    if ($tags)
        $post['tags_input'] = implode(', ', $tags);
    $post['post_category'] = [get_cat_ID($category)];
    $post['meta_input'] = ['comments' => $comments];

    // Write post
    $postId = wp_insert_post($post);
    // $logger->send_log('Post "' . $title . '(' . $postId . ')" has been created.');
    Generate_Featured_Image($thumbnail, $postId, $logger);

    $content_update = str_replace('[Tags]', '[mam_tag id=' . $postId . ']', $content);
    $content_update = str_replace('[twitter]', $tw_result, $content_update);
    $content_update = str_replace('[pinterest]', $pi_result, $content_update);
    $content_update = str_replace('[flickr]', $fl_result, $content_update);

    $mypost = array(
        'ID' => $postId,
        'post_content' => $content_update,
    );

    wp_update_post($mypost);
    if (isset($comments)) {
        foreach ($comments as $comment) {
            $snippet = $comment['snippet']['topLevelComment']['snippet'];
            $result = wp_insert_comment(array(
                'comment_post_ID' => $postId,
                'comment_author' => $snippet['authorDisplayName'],
                'comment_content' => $snippet['textDisplay'],
                'comment_type' => '',
                'comment_parent' => NULL,
                'comment_date' => convertYoutubeDate($snippet['publishedAt']),
                'comment_date_gmt' => convertYoutubeDate($snippet['publishedAt'], TRUE),
                'comment_approved' => 1,
            ));
            if (isset($comment['replies']['comments'])) {
                foreach ($comment['replies']['comments'] as $reply) {
                    $res = wp_insert_comment(array(
                        'comment_post_ID' => $postId,
                        'comment_author' => $reply['snippet']['authorDisplayName'],
                        'comment_content' => $reply['snippet']['textDisplay'],
                        'comment_type' => '',
                        'comment_parent' => $result,
                        'comment_date' => convertYoutubeDate($reply['snippet']['publishedAt']),
                        'comment_date_gmt' => convertYoutubeDate($reply['snippet']['publishedAt'], TRUE),
                        'comment_approved' => 1,
                    ));
                }
            }
        }
    }
}

function convertYoutubeDate($date, $gmt = FALSE)
{
    $result = strtotime($date);
    if (!$result) {
        return NULL;
    }

    if (!$gmt) {
        $result += get_option('gmt_offset') * HOUR_IN_SECONDS;
    }

    return gmdate('Y-m-d H:i:s', $result);
}

function Generate_Featured_Image($image_url, $post_id, $logger)
{
    $file_arr = explode('.', basename($image_url));
    $filename = $file_arr[0] . date('Ymdhisa') . '.' . $file_arr[1];
    $uploaddir = wp_upload_dir();
    $uploadfile = $uploaddir['path'] . '/' . $filename;

    $contents = file_get_contents($image_url);
    $savefile = fopen($uploadfile, 'w');
    fwrite($savefile, $contents);
    fclose($savefile);
    $wp_filetype = wp_check_filetype(basename($filename), null);

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $filename,
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $uploadfile);

    $imagenew = get_post($attach_id);
    $fullsizepath = get_attached_file($imagenew->ID);
    if (!$fullsizepath) {
        // $logger->send_log('The featured image of post ' . $post_id . ' is not attached.');
    } else {
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }
}

function get_rss_fetch($title, $limit, $logger)
{
    $keyword = str_replace(' ', '-', $title);
    $rss = new DOMDocument();
    $rss->load('https://news.google.com/rss/search?q=' . $keyword);
    $result = '';
    $feed = array();
    foreach ($rss->getElementsByTagName('item') as $node) {
        $item = array(
            'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
            'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
            'content' => $node->getElementsByTagName('description')->item(0)->nodeValue,
            'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
            'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
        );
        $content = $node->getElementsByTagName('encoded'); // <content:encoded>
        if ($content->length > 0) {
            $item['content'] = $content->item(0)->nodeValue;
        }
        array_push($feed, $item);
    }
    // real good count
    $max_item_cnt = count($feed) > $limit ? $limit : count($feed);
    $result .= '<ul class="feed-lists">';
    for ($x = 0; $x < $max_item_cnt; $x++) {
        $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $link = $feed[$x]['link'];
        $result .= '<li class="feed-item">';
        $result .= '<div class="feed-title"><strong><a href="' . $link . '" title="' . $title . '">' . $title . '</a></strong></div>';
        // if ($show_date) {
        $date = date('l F d, Y', strtotime($feed[$x]['date']));
        $result .= '<small class="feed-date"><em>Posted on ' . $date . '</em></small>';
        // }
        // if ($show_description) {
        $description = $feed[$x]['desc'];
        $content = $feed[$x]['content'];
        // find the img
        $has_image = preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);
        // no html tags
        $description = strip_tags(preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', "$1$3", $description), '');
        // whether cut by number of words
        // if ($max_words > 0) {
        $arr = explode(' ', $description);
        // if ($max_words < count($arr)) {
        $description = '';
        $w_cnt = 0;
        foreach ($arr as $w) {
            $description .= $w . ' ';
            $w_cnt = $w_cnt + 1;
            // if ($w_cnt == $max_words) {
            //     break;
            // }
        }
        $description .= " ...";
        // }
        // }
        // add img if it exists
        if ($has_image == 1) {
            $description = '<img class="feed-item-image" src="' . $image['src'] . '" />' . $description;
        }
        $result .= '<div class="feed-description">' . $description;
        $result .= ' <a href="' . $link . '" title="' . $title . '">Continue Reading &raquo;</a>' . '</div>';
        // }
        $result .= '</li>';
    }
    $result .= '</ul>';
    return $result;
}

function get_pinterest_fetch($pi_info, $pinterest_limit, $logger)
{
    $count = $pinterest_limit;
    $pin_keyword = $pi_info['pi_keyword'];
    $key = $pi_info['pi_api_key'];
    $pin_keyword = str_replace(' ', '', $pin_keyword);
    $rss = new DOMDocument();

    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );
    try {
        $rss->load('https://www.pinterest.com/' . $pin_keyword . '/feed.rss');
    } catch (Exception $e) {
        $logger->send_log('Pinterest: Invalid Keyword');
        return '';
    }
    restore_error_handler();

    $pin_ids = array();
    $i = 0;
    foreach ($rss->getElementsByTagName('item') as $node) {
        $url = $node->getElementsByTagName('link')->item(0)->nodeValue;
        $url_arr = explode('/', $url);
        array_push($pin_ids, $url_arr[count($url_arr) - 2]);
        $i++;
        if ($i > $count) break;
    }
    $result = '<div class="pi_container row">';
    foreach ($pin_ids as $id) {
        $url = 'https://api.pinterest.com/v1/pins/' . $id . '/?access_token=' . $key . '&fields=id,link,note,url,media,created_at,creator,image';
        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            }
        );
        try {
            $fetch_data = json_decode(file_get_contents($url));
        } catch (Exception $e) {
            $logger->send_log('Pinterest: Invalid Access Token');
            return '';
        }
        restore_error_handler();
        $data = $fetch_data->data;
        $name = $data->creator->last_name;
        $user_url = $data->creator->url;
        $target_url = $data->url;
        $img_url = $data->image->original->url;
        $text = $data->note;
        $date = $data->created_at;

        $result .= '<div class="mam_show_item col-md-12">
        <div class="mam-icon-spacer"></div>
        <div class="mam-icon-wrap">
            <div class="mam_item_user_name row">
                <div class="col-md-8 mam_name_left">
                    <a href="' . $user_url . '" target="_blank">' . $name . '</a><br>
                    <span class="mam_item_post_time">' . $date . '</span>
                </div>
                <div class="col-md-4 mam_name_right">
                    <a href="' . $target_url . '" target="_blank">
                        <i class="flaticon-pinterest"></i>
                    </a>
                </div>
            </div>
        </div>
        <br>
        <div class="mam_item_description">
            ' . $text . '
            <a href="' . $target_url . '" target="_blank">
                ' . $target_url . '
            </a>
        </div>
        <div class="mam_item_image">
            <a href="' . $target_url . '" class="" target="_blank">
                <img class="mam_item_description_image" src="' . $img_url . '" alt="' . $name . ' photo">
            </a>
        </div>';
        $result .= '</div>';
    }
    $result .= '</div>';
    return $result;
}

function get_twitter_fetch($keyword, $tw_info, $twitter_limit, $logger)
{
    $consumer_key = $tw_info['tw_api_key'];
    $consumer_secret = $tw_info['tw_api_secret'];
    $access_token = $tw_info['tw_access_token'];
    $access_token_secret = $tw_info['tw_token_secret'];
    $count = $twitter_limit;

    // Connect to API
    $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
    $content = $connection->get("account/verify_credentials");

    if (isset($content->errors)) {
        $logger->send_log('Twitter: Invalid Auth information');
        return '';
    }

    // Get tweets
    $statuses = $connection->get("search/tweets", [
        "count" => $count,
        "exclude_replies" => true,
        "q" => $keyword,
        "result_type" => "recent",
        "include_entities" => true,
        "tweet_mode" => "extended",
    ]);

    $statuses = $statuses->statuses;

    $result = '<div class="tw_container row">';
    foreach ($statuses as $status) {
        $url = isset($status->entities->urls[0]) ? $status->entities->urls[0]->url : '';
        $media = isset($status->entities->media) ? $status->entities->media[0]->media_url : '';
        $text = isset($status->full_text) ? $status->full_text : '';
        $name = $status->user->screen_name;
        $avata = $status->user->profile_image_url;
        $date = $status->user->created_at;
        $result .= '<div class="mam_show_item col-md-12">
            <div class="mam-icon-spacer"></div>
            <div class="mam-icon-wrap">
                <div class="mam_item_user_name row">
                    <div class="col-md-8 mam_name_left">
                        <img class="mam_item_avata" src="' . $avata . '">
                        <a href="https://twitter.com/' . $name . '" target="_blank">' . $name . '</a>
                        <span class="mam_item_post_time">' . $date . '</span>
                    </div>
                    <div class="col-md-4 mam_name_right">
                        <a href="' . $url . '" target="_blank">
                            <i class="flaticon-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <div class="mam_item_description">
                ' . $text . '
                <a href="' . $url . '" target="_blank">
                    ' . $url . '
                </a>
            </div>';
        if ($media != '')
            $result .= '<div class="mam_item_image">
                <a href="' . $url . '" class="" target="_blank">
                    <img class="mam_item_description_image" src="' . $media . '" alt="' . $name . ' photo">
                </a>
            </div>';
        $result .= '</div>';
    }
    $result .= '</div>';

    return $result;
}

function get_flickr_fetch($keyword, $key, $flickr_limit, $logger)
{
    $count = $flickr_limit;
    $keyword = str_replace(' ', '%20', $keyword);
    $base_url = 'https://api.flickr.com/services/rest/?method=';

    $photo_array = getArrayFromXML($base_url . 'flickr.photos.search&per_page=' . $count . '&api_key=' . $key . '&text=' . $keyword);

    if ($photo_array['@attributes']['stat'] != "ok") {
        $logger->send_log('Flickr: ' . $photo_array['err']['@attributes']['msg']);
        return '';
    }

    if (!isset($photo_array['photos']['photo'])) return '';

    $photos = $photo_array['photos']['photo'];
    $photo_ids = array();
    foreach ($photos as $photo) {
        array_push($photo_ids, $photo['@attributes']['id']);
    }

    $result = '<div class="fl_container row">';
    foreach ($photo_ids as $id) {
        $info_array = getArrayFromXML($base_url . 'flickr.photos.getInfo&photo_id=' . $id . '&api_key=' . $key);
        $info = $info_array['photo'];
        $name = $info['owner']['@attributes']['username'];
        $user_url = 'https://www.flickr.com/photos/' . $info['owner']['@attributes']['path_alias'];
        $target_url = $info['urls']['url'];
        $img_url = 'http://farm' . $info['@attributes']['farm'] . '.staticflickr.com/' . $info['@attributes']['server'] . '/' . $id . '_' . $info['@attributes']['secret'] . '_b.jpg';
        $text = $info['title'];

        $result .= '<div class="mam_show_item col-md-12">
        <div class="mam-icon-spacer"></div>
        <div class="mam-icon-wrap">
            <div class="mam_item_user_name row">
                <div class="col-md-8 mam_name_left">
                    <a href="' . $user_url . '" target="_blank">' . $name . '</a><br>
                </div>
                <div class="col-md-4 mam_name_right">
                    <a href="' . $target_url . '" target="_blank">
                        <i class="flaticon-flickr"></i>
                    </a>
                </div>
            </div>
        </div>
        <br>
        <div class="mam_item_description">
            ' . $text . '<br>
        </div>
        <div class="mam_item_image">
            <a href="' . $target_url . '" class="" target="_blank">
                <img class="mam_item_description_image" src="' . $img_url . '" alt="' . $name . ' photo">
            </a>
        </div>';
        $result .= '</div>';
    }
    $result .= '</div>';
    return $result;
}

function getArrayFromXML($url)
{
    $xml_string = file_get_contents($url);
    $xml = simplexml_load_string($xml_string);
    $json = json_encode($xml);
    return json_decode($json, TRUE);
}
