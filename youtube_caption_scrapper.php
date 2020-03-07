<?php

use function Sodium\hex2bin;

$path = '../../../';

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

include_once './Library/MamLogger.php';

$logger = MamLogger::get_instance();
global $wpdb;

if ($_POST['kind'] == 'create_camp') {
    $camp_name = $_POST['camp_name'];
    $temp_name = $_POST['temp_name'];
    $keyword = $_POST['keyword'];

    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates where name="' . $temp_name . '"';
    $temp_content = $wpdb->get_row($sql)->content;

    if ($video_result = get_video_id($keyword, $logger)) {
        $video_id = $video_result['video_id'];
        $tags = $video_result['tags'];
        $thumbnail = $video_result['thumbnail'];
        $comments = get_video_comments($video_id);
        $caption = get_youtube_caption($video_id);

        if ($_POST['flag_fl'] == 1)
            $caption = removeFirstSentence($caption);
        if ($_POST['flag_ll'] == 1)
            $caption = removeLastSentence($caption);

        $caption = insertLineBreak($caption);

        echo json_encode(array(
            'temp_content' => $temp_content,
            'video_id' => $video_id,
            'caption' => $caption,
            'title' => $keyword,
            'tags' => $tags,
            'comments' => $comments,
            'thumbnail' => $thumbnail
        ));
    } else {
        echo json_encode(array(
            'temp_content' => $temp_content,
            'video_id' => '',
            'caption' => '',
            'title' => $keyword,
            'tags' => '',
            'comments' => '',
            'thumbnail' => ''
        ));
    }
} else if ($_POST['kind'] == 'create_video') {
    $temp_name = $_POST['temp_name'];
    $video_id = $_POST['video_id'];

    $sql = 'select * from ' . $wpdb->prefix . 'mam_templates where name="' . $temp_name . '"';
    $temp_content = $wpdb->get_row($sql)->content;

    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
    $auth = $wpdb->get_row($sql);
    $key = $auth->api_key;

    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );
    try {
        $yt_tag_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $video_id . '&key=' . $key;
        $tag_json = file_get_contents($yt_tag_url);
    } catch (Exception $e) {
        $logger->send_log("Video: Invalid Youtube Google API or Invalid Video Url");
        return '';
    }
    restore_error_handler();

    $data = json_decode($tag_json, true);
    $thumbnail = $data['items'][0]['snippet']['thumbnails']['standard']['url'];
    $tags = $data['items'][0]['snippet']['tags'];
    $title = $data['items'][0]['snippet']['title'];

    $comments = get_video_comments($video_id);
    $caption = get_youtube_caption($video_id);

    if ($_POST['flag_fl'] == 1)
        $caption = removeFirstSentence($caption);
    if ($_POST['flag_ll'] == 1)
        $caption = removeLastSentence($caption);

    $caption = insertLineBreak($caption);

    echo json_encode(array(
        'temp_content' => $temp_content,
        'title' => $title,
        'caption' => $caption,
        'tags' => $tags,
        'comments' => $comments,
        'thumbnail' => $thumbnail
    ));
}

function get_video_id($keyword, $logger)
{
    global $wpdb;
    $keyword = str_replace(" ", "%20", $keyword);
    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
    $auth = $wpdb->get_row($sql);
    $key = $auth->api_key;
    $video_id = array();
    $tags = array();
    $count = 20;

    $yt_base_url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=' . $count . '&order=viewCount';
    $yt_url = $yt_base_url . '&key=' . $key . '&q=' . $keyword;
    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );
    try {
        $data_json = file_get_contents($yt_url);
    } catch (Exception $e) {
        $logger->send_log("Youtube: Invalid Youtube Google API");
        return '';
    }
    restore_error_handler();
    $data = json_decode($data_json, true);

    $i = 0;
    $select_seconds = 0;
    do {
        $item = $data['items'][$i];
        $video_id = $item['id']['videoId'];
        $thumb = $item['snippet']['thumbnails']['high']['url'];
        $video_info = file_get_contents('https://www.youtube.com/get_video_info?&video_id=' . $video_id);
        parse_str($video_info, $video_info_array);
        $lengthSeconds = json_decode($video_info_array['player_response'])->videoDetails->lengthSeconds;
        $cc_link = json_decode($video_info_array['player_response'])->captions->playerCaptionsTracklistRenderer->captionTracks[0]->baseUrl;
        if ($cc_link != '' && $select_seconds < intval($lengthSeconds)) {
            $select_videoId = $video_id;
            $select_seconds = intval($lengthSeconds);
            $select_thumb = $thumb;
        }
        $i++;
    } while (($cc_link == '' || intval($lengthSeconds) < 5 * 60) && $i < ($count - 1));

    if ($i == ($count - 1)) {
        $video_id = $select_videoId;
        $thumb = $select_thumb;
    }

    $yt_tag_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $video_id . '&key=' . $key;
    $tag_json = file_get_contents($yt_tag_url);
    $data = json_decode($tag_json, true);
    $tags = $data['items'][0]['snippet']['tags'];

    return array('video_id' => $video_id, 'tags' => $tags, 'thumbnail' => $thumb);
}
function hyphenize($string)
{
    return
        ## strtolower(
        preg_replace(
            array('#[\\s-]+#', '#[^A-Za-z0-9\. -]+#'),
            array(' ', ''),
            ##     cleanString(
            urldecode($string)
            ##     )
        )
        ## )
    ;
}
function get_youtube_caption($video_id)
{
    $video_info = file_get_contents('https://www.youtube.com/get_video_info?&video_id=' . $video_id);

    parse_str($video_info, $video_info_array);

    $cc_link = json_decode($video_info_array['player_response'])->captions->playerCaptionsTracklistRenderer->captionTracks[0]->baseUrl;
    if ($cc_link != '') {
        $text = file_get_contents($cc_link);
        $text = preg_replace('/<\?xml version="1.0" encoding="utf-8" \?><transcript>/', '', $text);
        $text = preg_replace('/<\/transcript>/', '', $text);
        $text = preg_replace('/(<text start=([^>]+)">)/U', '<P>', $text);
        $text = preg_replace('/<\/text>/', '</P>', $text);

        $text = str_ireplace('</P>', "</P>\n", $text);
        $text = html_entity_decode($text);
        $text = strip_tags($text);
        $text = str_ireplace("\n", " ", $text);
        $text = hyphenize($text);

        $ch = curl_init('http://bark.phon.ioc.ee/punctuator');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "text=" . $text);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $text = '';
        } else {
            $text = str_ireplace('39', "'", $response);
        }
        curl_close($ch);
        return $text;
    }
    return '';
}

function get_video_comments($id, $params = array())
{
    $params['videoId'] = $id;

    if (!isset($params['part'])) {
        $params['part'] = 'snippet';
    }

    global $wpdb;

    $sql = 'select * from ' . $wpdb->prefix . 'mam_auth where user_id=' . get_current_user_id();
    $auth = $wpdb->get_row($sql);
    $key = $auth->api_key;

    $url = 'https://www.googleapis.com/youtube/v3/commentThreads?key=' . $key . '&textFormat=plainText&part=snippet,replies&videoId=' . $id;

    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 60,
    ));

    $result = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    $response = json_decode($result, true);
    if (isset($response['error']['errors']))
        return array();
    else
        return $response['items'];
}

function insertLineBreak($text)
{
    // $lb = hex2bin("0d0a");
    $lb = hextobin("0d0a");
    $result = "";
    $count_space = 0;
    for ($i = 0; $i < strlen($text); $i++) {
        $letter = substr($text, $i, 1);
        $result .= $letter;
        if ($letter == " ") $count_space++;
        if ($count_space > 250 && $letter == '.') {
            $result .= $lb . $lb;
            $count_space = 0;
        }
    }
    return $result;
}

function hextobin($hexstr)
{
    $n = strlen($hexstr);
    $sbin = "";
    $i = 0;
    while ($i < $n) {
        $a = substr($hexstr, $i, 2);
        $c = pack("H*", $a);
        if ($i == 0) {
            $sbin = $c;
        } else {
            $sbin .= $c;
        }
        $i += 2;
    }
    return $sbin;
}

function removeFirstSentence($text)
{
    $result = "";
    $flag = 0;
    for ($i = 0; $i < strlen($text); $i++) {
        $letter = substr($text, $i, 1);
        if ($flag == 1)
            $result .= $letter;
        if ($letter == '.' && $i > 10) {
            $flag = 1;
        }
    }
    return $result;
}
function removeLastSentence($text)
{
    for ($i = strlen($text) - 10; $i > 0; $i--) {
        $letter = substr($text, $i, 1);
        if ($letter == ".") break;
    }
    if ($i > 1)
        $result = substr($text, 0, $i + 1);
    else $result = "";
    return $result;
}
