<?php

namespace jm_twitter_cards;

if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Options{

    /**
     * options
     * @var array
     */
    protected $opts = array();


    /**
     * Only allow a publisher to define a valid card type
     *
     * @since 1.0
     * @var array
     */
    public static $allowed_card_types = array( 'summary' => true, 'summary_large_image' => true, 'photo' => true, 'gallery' => true, 'player' => true, 'product' => true, 'app' => true );


    /**
     * Constructor
     * @since 5.3.2
     */
    function __construct(){

        $this->opts = jm_tc_get_options();

    }


    /**
     * Retrieve the meta card type
     * @param bool $post->ID
     * @return array
     */
    public function cardType(\WP_Post $post){

        $cardTypePost = get_post_meta($post->ID, 'twitterCardType', true);

        $cardType = '' !== $cardTypePost && is_string($cardTypePost) ? $cardTypePost : $this->opts['twitterCardType'];
        $cardType =  apply_filters('jm_tc_card_type', $cardType);

        //in case filter is misused
        if( isset( self::$allowed_card_types[$cardType] ) ) {
           return array('card' => $cardType);
        }

        return array('card' => 'summary');
    }

    /**
     * Retrieve the meta creator
     * @param bool $post_author
     * @param \WP_Post $post
     * @return array
     */

    public function creatorUsername($post_author = false, \WP_Post $post){

        $cardCreator = '@' . Utilities::remove_at($this->opts['twitterCreator']);
        $post = get_post($post->ID);

        if ( false !== $post_author) {

            //to be modified or left with the value 'jm_tc_twitter'

            $cardUsernameKey = $this->opts['twitterUsernameKey'];
            $cardCreator = get_the_author_meta($cardUsernameKey, $post->post_author);

            $cardCreator = '' !== $cardCreator ? $cardCreator : $this->opts['twitterCreator'];
            $cardCreator = '@' . Utilities::remove_at($cardCreator);

        }

        $cardCreator = apply_filters('jm_tc_card_creator', $cardCreator );

        return array('creator' => $cardCreator);
    }

    /**
     * retrieve the meta site
     * @return array
     */
    public function siteUsername(){

        $cardSite = '@' . Utilities::remove_at($this->opts['twitterSite']);
        $cardSite = apply_filters('jm_tc_card_site', $cardSite);

        return array('site' =>  $cardSite);
    }

    /**
     *
     * @param $post->ID
     * @param $type
     * @return bool|string
     */
    public static function get_seo_plugin_data(\WP_Post $post, $type){

        if (class_exists('WPSEO_Frontend')) {
            $title = Utilities::strip_meta('_yoast_wpseo_title', $post->ID);
            $desc = Utilities::strip_meta('_yoast_wpseo_metadesc', $post->ID);

        } elseif (class_exists('All_in_One_SEO_Pack')) {
            $title = Utilities::strip_meta('_aioseop_title', $post->ID);
            $desc = Utilities::strip_meta('_aioseop_description', $post->ID);
        }

        if( 'title' === $type ) {
           return $title;
        }

        return $desc;

    }

    /**
     * retrieve the title
     * @param \WP_Post $post
     * @return array
     */
    public function title(\WP_Post $post){

        $cardTitle = the_title_attribute(array('echo' => false));
        $customCardTitle = Utilities::strip_meta($this->opts['twitterCardTitle'], $post->ID);

        if ( class_exists('WPSEO_Frontend') || class_exists('All_in_One_SEO_Pack') ) {

            $seo_title = self::get_seo_plugin_data($post->ID, 'title');
            $cardTitle = false !== $seo_title ? $seo_title : the_title_attribute(array('echo' => false));

        }

        if ( '' !== $this->opts['twitterCardTitle'] && !is_null($this->opts['twitterCardTitle']) ) {

            $cardTitle = false !== $customCardTitle ? $customCardTitle : the_title_attribute(array('echo' => false));

        }


        $cardTitle = apply_filters('jm_tc_get_title', $cardTitle );

        return array('title' =>  $cardTitle);

    }

    /**
     * retrieve the description
     * @param \WP_Post $post
     * @return array
     */
    public function description(\WP_Post $post){

        $cardDescription = Utilities::get_excerpt_by_id($post->ID);
        $customCardDescription = Utilities::strip_meta($this->opts['twitterCardDesc'], $post->ID);

        if ( class_exists('WPSEO_Frontend') || class_exists('All_in_One_SEO_Pack') ) {

            $seo_desc = self::get_seo_plugin_data($post->ID, 'description');
            $cardDescription = false !== $seo_desc ? $seo_desc : Utilities::get_excerpt_by_id($post->ID);

        }


        if ( '' !== $this->opts['twitterCardDesc'] && !is_null($this->opts['twitterCardDesc']) ) {

            $cardDescription = false !== $customCardDescription ? $customCardDescription : Utilities::get_excerpt_by_id($post->ID);

        }

        $cardDescription = apply_filters( 'jm_tc_get_excerpt', Utilities::remove_lb($cardDescription) );

        return array('description' => $cardDescription);

    }


    /**
     * retrieve the image
     * @param \WP_Post $post
     * @return array|bool|string
     */
    public function image(\WP_Post $post){

        $cardImage = get_post_meta($post->ID, 'cardImage', true);
        $cardType = get_post_meta($post->ID, 'twitterCardType', true);
        $image = $this->opts['twitterImage'];

        if( is_string($cardType) && 'gallery' === $cardType ) {

            $query_img = get_post_gallery($post->ID, false);//get_post_gallery already checks for $post and has_shortcode()

            if( is_array($query_img) ) {

                $pic = array();
                $i = 0;

                foreach ($query_img['src'] as $img) {

                    // get attachment array with the ID from the returned posts

                    $pic['image' . $i . ':src'] = $img;

                    $i++;
                    if ($i > 3) break; //in case there are more than 4 images in post, we are not allowed to add more than 4 images in our card by Twitter

                }

                return $pic;

            }

            return self::error(__('Warning : Gallery Card is not set properly ! There is no gallery in this post !', JM_TC_TEXTDOMAIN));

        }

        if ('' !== $cardImage) {
            $image = $cardImage;
        }

        if ('' !== get_the_post_thumbnail($post->ID)) {

            $size = Thumbs::thumbnail_sizes($post->ID);
            $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
            $image = $image_attributes[0];

            if ('' !== $cardImage) { // cardImage is set
                $image = $cardImage;
            }

        }

        if ('attachment' === get_post_type()) {

            $image = wp_get_attachment_url($post->ID);
        }

        //In case Open Graph is on
        $img_meta = 'yes' === $this->opts['twitterCardOg'] ? 'image' : 'image:src';
        $image = apply_filters('jm_tc_image_source', $image);

        return array($img_meta => $image);

    }


    /**
     * Product additional fields
     * @param \WP_Post $post
     * @return array|bool|string
     */
    public function product(\WP_Post $post){

        $cardType = apply_filters('jm_tc_card_type', get_post_meta($post->ID, 'twitterCardType', true));

        if ('product' === $cardType) {

            $data1  = apply_filters( 'jm_tc_product_field-data1',get_post_meta($post->ID, 'cardData1', true) );
            $label1 = apply_filters( 'jm_tc_product_field-label1', get_post_meta($post->ID, 'cardLabel1', true) );
            $data2  = apply_filters( 'jm_tc_product_field-data2', get_post_meta($post->ID, 'cardData2', true) );
            $label2 = apply_filters( 'jm_tc_product_field-label2', get_post_meta($post->ID, 'cardLabel2', true) );

            if ( '' !== $data1 && '' !== $label1 && '' !== $data2 && '' !== $label2 ) {
                return array(
                    'data1' => $data1,
                    'label1' => $label1,
                    'data2' => $data2,
                    'label2' => $label2,
                );
            }

            return self::error(__('Warning : Product Card is not set properly ! There is no product datas !', JM_TC_TEXTDOMAIN));

        }

        return false;
    }

    /**
     * Player additional fields
     * @param \WP_Post $post
     * @return array|bool|string
     */
    public function player(\WP_Post $post){

        $cardType = apply_filters( 'jm_tc_card_type', get_post_meta($post->ID, 'twitterCardType', true));

        if ('player' === $cardType ) {

            $playerUrl = apply_filters( 'jm_tc_player_url', get_post_meta($post->ID, 'cardPlayer', true) );
            $playerStreamUrl = apply_filters( 'jm_tc_player_stream_url', get_post_meta($post->ID, 'cardPlayerStream', true) );
            $playerWidth = apply_filters( 'jm_tc_player_width', get_post_meta($post->ID, 'cardPlayerWidth', true) );
            $playerHeight = apply_filters( 'jm_tc_player_height', get_post_meta($post->ID, 'cardPlayerHeight', true) );
            $defaultPlayerWidth = apply_filters('jm_tc_player_default_width', 435 );
            $defaultPlayerHeight = apply_filters('jm_tc_player_default_height', 251 );

            $player = array();
            $player['player:width'] = $defaultPlayerWidth;
            $player['player:height'] = $defaultPlayerHeight;
            $player['player'] =  $playerUrl;


            //Player
            if ( '' === $playerUrl ) {
                return self::error(__('Warning : Player Card is not set properly ! There is no URL provided for iFrame player !', JM_TC_TEXTDOMAIN));
            }

            //Player stream
            if ( '' !== $playerStreamUrl ) {

                $codec = apply_filters( 'jm_tc_player_codec', 'video/mp4; codecs=&quot;avc1.42E01E1, mp4a.40.2&quot;' );

                $player['player:stream'] = $playerStreamUrl;
                $player['player:stream:content_type'] =  $codec;

            }

            if ( '' !== $playerWidth && '' !== $playerHeight ) {
                $player['player:width'] = $playerWidth;
                $player['player:height'] = $playerHeight;

            }

            return $player;

        }

        return false;

    }


    /**
     * Image Width and Height
     * @param \WP_Post $post
     * @return array|bool
     */

    public function cardDim(\WP_Post $post){

        $cardTypePost = get_post_meta($post->ID, 'twitterCardType', true);
        $cardWidth = get_post_meta($post->ID, 'cardImageWidth', true);
        $cardHeight = get_post_meta($post->ID, 'cardImageHeight', true);
        $type = '' !== $cardTypePost && is_string($cardTypePost) ? $cardTypePost : $this->opts['twitterCardType'];

        if (in_array($type, array('photo', 'product', 'summary_large_image', 'player'))) {

            $width = '' !== $cardWidth ?  apply_filters( 'jm_tc_image_width', $cardWidth ) : $this->opts['twitterImageWidth'];
            $height = '' !== $cardHeight ? apply_filters( 'jm_tc_image_height', $cardHeight ) : $this->opts['twitterImageHeight'];

            return array(
                'image:width' => $width,
                'image:height' => $height,
            );

        }

        return false;
    }


    /**
     * retrieve the deep linking and app install meta
     * @return array
     */
    public function deeplinking(){

        $twitteriPhoneName = (!empty($this->opts['twitteriPhoneName'])) ? apply_filters( 'jm_tc_iphone_name', $this->opts['twitteriPhoneName'] ) : '';
        $twitteriPadName = (!empty($this->opts['twitteriPadName'])) ? apply_filters( 'jm_tc_ipad_name', $this->opts['twitteriPadName'] ) : '';
        $twitterGooglePlayName = (!empty($this->opts['twitterGooglePlayName'])) ? apply_filters('jm_tc_googleplay_name', $this->opts['twitterGooglePlayName'] ) : '';
        $twitteriPhoneUrl = (!empty($this->opts['twitteriPhoneUrl'])) ? apply_filters('jm_tc_iphone_url', $this->opts['twitteriPhoneUrl'] ) : '';
        $twitteriPadUrl = (!empty($this->opts['twitteriPadUrl'])) ? apply_filters('jm_tc_ipad_url', $this->opts['twitteriPadUrl'] ) : '';
        $twitterGooglePlayUrl = (!empty($this->opts['twitterGooglePlayUrl'])) ? apply_filters('jm_tc_googleplay_url', $this->opts['twitterGooglePlayUrl'] ) : '';
        $twitteriPhoneId = (!empty($this->opts['twitteriPhoneId'])) ? apply_filters('jm_tc_iphone_id', $this->opts['twitteriPhoneId'] ) : '';
        $twitteriPadId = (!empty($this->opts['twitteriPadId'])) ? apply_filters('jm_tc_ipad_id', $this->opts['twitteriPadId'] ) : '';
        $twitterGooglePlayId = (!empty($this->opts['twitterGooglePlayId'])) ? apply_filters('jm_tc_googleplay_id', $this->opts['twitterGooglePlayId'] ) : '';
        $twitterAppCountry = (!empty($this->opts['twitterAppCountry'])) ? apply_filters('jm_tc_country', $this->opts['twitterAppCountry'] ) : '';


        return array(
            'app:name:iphone' => $twitteriPhoneName,
            'app:name:ipad' => $twitteriPadName,
            'app:name:googleplay' =>  $twitterGooglePlayName,
            'app:url:iphone' => $twitteriPhoneUrl,
            'app:url:ipad' =>  $twitteriPadUrl,
            'app:url:googleplay' => $twitterGooglePlayUrl,
            'app:id:iphone' => $twitteriPhoneId,
            'app:id:ipad' => $twitteriPadId,
            'app:id:googleplay' => $twitterGooglePlayId,
            'app:id:country' => $twitterAppCountry,
        );


    }


    /**
     * @param string $error
     * @return bool|string
     */
    protected function error($error = ''){

        if ( '' !== $error && current_user_can( 'edit_posts' ) ) {
            return $error;
        }

        return false;
    }


}