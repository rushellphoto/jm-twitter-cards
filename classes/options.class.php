<?php
if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if ( !class_exists('JM_TC_Options') ) {

    class JM_TC_Options{

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
         * @param bool $post_ID
         * @return array
         */
        public function cardType($post_ID = false){

            $cardTypePost = get_post_meta($post_ID, 'twitterCardType', true);

            $cardType = '' !== $cardTypePost ? $cardTypePost : $this->opts['twitterCardType'];
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
         * @param bool $post_ID
         * @return array
         */

        public function creatorUsername($post_author = false, $post_ID = false){

            $post = get_post($post_ID);
            $author_id = $post->post_author;

            $cardCreator = '@' . JM_TC_Utilities::remove_at($this->opts['twitterCreator']);

            if ( false !== $post_author) {

                //to be modified or left with the value 'jm_tc_twitter'

                $cardUsernameKey = $this->opts['twitterUsernameKey'];
                $cardCreator = get_the_author_meta($cardUsernameKey, $author_id);

                $cardCreator = '' !== $cardCreator ? $cardCreator : $this->opts['twitterCreator'];
                $cardCreator = '@' . JM_TC_Utilities::remove_at($cardCreator);

            }

            $cardCreator = apply_filters('jm_tc_card_creator', $cardCreator );

            return array('creator' => $cardCreator);
        }

        /**
         * retrieve the meta site
         * @return array
         */
        public function siteUsername(){

            $cardSite = '@' . JM_TC_Utilities::remove_at($this->opts['twitterSite']);
            $cardSite = apply_filters('jm_tc_card_site', $cardSite);

            return array('site' =>  $cardSite);
        }

        /**
         * Retrieve plugin data with fallbacks
         * @param $post_ID
         * @param $type
         * @return bool|null|string|void
         */
        public static function get_seo_plugin_data($post_ID, $type){

            if (class_exists('WPSEO_Frontend')) {
                $title = JM_TC_Utilities::strip_meta('_yoast_wpseo_title', $post_ID);
                $desc = JM_TC_Utilities::strip_meta('_yoast_wpseo_metadesc', $post_ID);

            } elseif (class_exists('All_in_One_SEO_Pack')) {
                $title = JM_TC_Utilities::strip_meta('_aioseop_title', $post_ID);
                $desc = JM_TC_Utilities::strip_meta('_aioseop_description', $post_ID);
            }

           if( 'title' === $type ) {
               return false !== $title ? $title :  the_title_attribute( array('echo' => false) );
           }

            return false !== $desc ? $desc : JM_TC_Utilities::get_excerpt_by_id($post_ID);

        }

        /**
         * retrieve the title
         * @param bool $post_ID
         * @return array
         */
        public function title($post_ID = false){

            $cardTitle = get_bloginfo('name');

            if (false !== $post_ID) {

                $cardTitle = the_title_attribute(array('echo' => false));
                $customCardTitle = JM_TC_Utilities::strip_meta($this->opts['twitterCardTitle'], $post_ID);

                if ( class_exists('WPSEO_Frontend') || class_exists('All_in_One_SEO_Pack') ) {

                    $cardTitle = self::get_seo_plugin_data($post_ID, 'title');

                }

                if ( '' !== $this->opts['twitterCardTitle'] && !is_null($this->opts['twitterCardTitle']) ) {

                    $cardTitle = false !==  $customCardTitle ? $customCardTitle : the_title_attribute(array('echo' => false));

                }

            }

            $cardTitle = apply_filters('jm_tc_get_title', $cardTitle );

            return array('title' =>  $cardTitle);

        }

        /**
         * retrieve the description
         * @param bool $post_ID
         * @return array
         */
        public function description($post_ID = false){

            $cardDescription = $this->opts['twitterPostPageDesc'];

            if (false !== $post_ID) {

                $cardDescription = JM_TC_Utilities::get_excerpt_by_id($post_ID);
                $customCardDescription = JM_TC_Utilities::strip_meta($this->opts['twitterCardDesc'], $post_ID);

                if ( class_exists('WPSEO_Frontend') || class_exists('All_in_One_SEO_Pack') ) {

                    $cardDescription = self::get_seo_plugin_data($post_ID, 'description');

                }


                if ( '' !== $this->opts['twitterCardDesc'] && !is_null($this->opts['twitterCardDesc']) ) {

                    $cardDescription = false !== $customCardDescription ? $customCardDescription : JM_TC_Utilities::get_excerpt_by_id($post_ID);

                }

            }

            $cardDescription = apply_filters( 'jm_tc_get_excerpt', JM_TC_Utilities::remove_lb($cardDescription) );

            return array('description' => $cardDescription);

        }


        /**
         * retrieve the images
         * @param bool $post_ID
         * @return array|bool|string
         */

        public function image($post_ID = false){

            $cardImage = get_post_meta($post_ID, 'cardImage', true);
            $cardType = get_post_meta($post_ID, 'twitterCardType', true);

            //gallery
            if ( 'gallery' !== $cardType ) {
                if ( '' !== get_the_post_thumbnail($post_ID) ) {
                    if (!empty($cardImage)) { // cardImage is set
                        $image = $cardImage;
                    } else {
                        $size = JM_TC_Thumbs::thumbnail_sizes($post_ID);
                        $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), $size);
                        $image = $image_attributes[0];
                    }

                } elseif (get_the_post_thumbnail($post_ID) == '' && !empty($cardImage)) {
                    $image = $cardImage;
                } elseif ('attachment' == get_post_type()) {

                    $image = wp_get_attachment_url($post_ID);
                } elseif ($post_ID == false) {

                    $image = $this->opts['twitterImage'];
                } else {
                    //fallback
                    $image = $this->opts['twitterImage'];
                }

                //In case Open Graph is on
                $img_meta = ('yes' === $this->opts['twitterCardOg']) ? 'image' : 'image:src';
                $image = apply_filters( 'jm_tc_image_source', $image );

                return array($img_meta => $image );

            }

            $post_obj = get_queried_object();

            if ( is_a($post_obj, 'WP_Post') && function_exists('has_shortcode') ) {

                if ( has_shortcode( $post_obj->post_content, 'gallery' ) ) {

                    $query_img = get_post_gallery() ? get_post_gallery($post_ID, false) : array();//no backward compatibility before 3.6

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

            return false;

        }


        /**
         * Product additional fields
         * @param $post_ID
         * @return array|bool|void
         */
        public function product($post_ID){

            $cardType = apply_filters('jm_tc_card_type', get_post_meta($post_ID, 'twitterCardType', true));

            if ('product' === $cardType) {

                $data1  = apply_filters( 'jm_tc_product_field-data1',get_post_meta($post_ID, 'cardData1', true) );
                $label1 = apply_filters( 'jm_tc_product_field-label1', get_post_meta($post_ID, 'cardLabel1', true) );
                $data2  = apply_filters( 'jm_tc_product_field-data2', get_post_meta($post_ID, 'cardData2', true) );
                $label2 = apply_filters( 'jm_tc_product_field-label2', get_post_meta($post_ID, 'cardLabel2', true) );

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
         * @param $post_ID
         * @return array|bool|void
         */
        public function player($post_ID){

            $cardType = apply_filters( 'jm_tc_card_type', get_post_meta($post_ID, 'twitterCardType', true));

            if ('player' === $cardType ) {

                $playerUrl = apply_filters( 'jm_tc_player_url', get_post_meta($post_ID, 'cardPlayer', true) );
                $playerStreamUrl = apply_filters( 'jm_tc_player_stream_url', get_post_meta($post_ID, 'cardPlayerStream', true) );
                $playerWidth = apply_filters( 'jm_tc_player_width', get_post_meta($post_ID, 'cardPlayerWidth', true) );
                $playerHeight = apply_filters( 'jm_tc_player_height', get_post_meta($post_ID, 'cardPlayerHeight', true) );
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
         * @param bool $post_ID
         * @return array|bool
         */

        public function cardDim($post_ID = false){

            $cardTypePost = get_post_meta($post_ID, 'twitterCardType', true);
            $cardWidth = get_post_meta($post_ID, 'cardImageWidth', true);
            $cardHeight = get_post_meta($post_ID, 'cardImageHeight', true);
            $type = '' !== $cardTypePost ? $cardTypePost : $this->opts['twitterCardType'];

            if (in_array($type, array('photo', 'product', 'summary_large_image', 'player'))) {

                $width = '' !== $cardWidth ?  apply_filters( 'jm_tc_image_width', $cardWidth ) : $this->opts['twitterImageWidth'];
                $height = '' !== $cardHeight ? apply_filters( 'jm_tc_image_height', $cardHeight ) : $this->opts['twitterImageHeight'];

                return array(
                    'image:width' => $width,
                    'image:height' => $height,
                );

            } elseif (in_array($type, array('photo', 'product', 'summary_large_image', 'player')) && false !== $post_ID) {

                return array(
                    'image:width' => $this->opts['twitterCardWidth'],
                    'image:height' => $this->opts['twitterCardHeight']
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


}