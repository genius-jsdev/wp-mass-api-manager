jQuery(document).ready(function(){
    var width = jQuery('.mam_campaign_main_container').width();
    jQuery('.mam_campaign_main_container').height(width / 4 * 3);
    jQuery('.show_caption_area').height('700px');

    jQuery('.mam_network_item').click(function(){
        jQuery('.mam_campaign_main_container').attr('src', jQuery(this).find('input').val());
    });

    jQuery(window).resize(function(){
        var width = jQuery('.mam_campaign_main_container').width();
        jQuery('.mam_campaign_main_container').height(width / 4 * 3);
    });

    jQuery('.btn_get_caption').click(function(){
        jQuery.ajax({
            url: './wp-content/plugins/mass-api-manager/youtube_caption_scrapper.php',
            data: { 'kind': 'show_caption', 'videoid': jQuery('.txt_video_id').val() },
            type: 'post',
            success: function (result) {
                jQuery('.show_caption_area').val(result)
            }
        });

    });
});

