<?php
namespace jm_twitter_cards;

if (!defined('JM_TC_VERSION')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class Markup{
    /**
     * Options
     * @var array
     */
    protected $opts = array();

    /**
     * Constructor
     * @since 5.3.2
     */
    function __construct(){

        $this->opts = jm_tc_get_options();

        add_action( 'wp_head', array($this, 'add_markup'), 2 );

    }

    /**
     * Add just one line before meta
     * @since 5.3.2
     * @param bool $end
     * @return string
     */
    public function html_comments($end = false){

        if (!$end)
            echo '<!-- JM Twitter Cards by Julien Maury ' . JM_TC_VERSION . ' -->' . PHP_EOL;
        else
            echo '<!-- /JM Twitter Cards ' . JM_TC_VERSION . ' -->' . PHP_EOL;
    }


    /**
     *  Add meta to head section
     */
    public function add_markup(){

        $options = new Options;

        if (
            is_singular()
            && !is_front_page()
            && !is_home()
            && !is_404()
            && !is_tag()
        ) {

            // safer than the global $post => seems killed on a lot of install :/
            $post_instance = $GLOBALS['post'];

            $this->html_comments();

            /* most important meta */
            $this->display_markup($options->cardType($post_instance));
            $this->display_markup($options->creatorUsername(true, $post_instance));
            $this->display_markup($options->siteUsername());
            $this->display_markup($options->title($post_instance));
            $this->display_markup($options->description($post_instance));
            $this->display_markup($options->image($post_instance));

            /* secondary meta */
            $this->display_markup($options->cardDim($post_instance));
            $this->display_markup($options->product($post_instance));
            $this->display_markup($options->player($post_instance));
            $this->display_markup($options->deeplinking());

            $this->html_comments('Marion');

        }

        if(is_home() || is_front_page()) {

            $this->html_comments();

            $this->display_markup(array('card' => $this->opts['twitterCardType']));
            $this->display_markup(array('creator' => $this->opts['twitterCreator']));
            $this->display_markup($options->siteUsername());
            $this->display_markup(array('title' => get_bloginfo('name')));
            $this->display_markup(array('description' => $this->opts['twitterPostPageDesc']));
            $this->display_markup(array('image' => $this->opts['twitterImage']));
            $this->display_markup(array('image:width' => $this->opts['twitterImageWidth'], 'image:height' => $this->opts['twitterImageHeight']));
            $this->display_markup($options->deeplinking());

            $this->html_comments('Marion');

        }

    }

    /**
     * Display the different meta
     * @param $data
     */
    protected function display_markup($data){

        if (is_array($data)) {

            foreach ($data as $name => $value) {

                if ( '' !== $value) {

                    $is_og = 'twitter';
                    $name_tag = 'name';

                    if ( 'yes' === $this->opts['twitterCardOg'] && in_array($name, array('title', 'description', 'image', 'image:width', 'image:height'))) {

                        $is_og = 'og';
                        $name_tag = 'property';

                    }

                    echo $meta = '<meta ' . sprintf('%3$s="%2$s:%1$s"',$name, $is_og, $name_tag ) . ' content="' . sprintf('%s', $value) . '">' . PHP_EOL;

                }

            }

        } elseif (is_string($data)) {

            echo $meta = sprintf('<!-- [(-_-)@ %s @(-_-)] -->', $data) . PHP_EOL;

        }

    }


}