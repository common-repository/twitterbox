<?php
/*
Plugin Name: TwitterBox
Plugin URI: http://wordpress.org/extend/plugins/twitterbox
Description: Twitter Widget listing matched hasht tweets
Version: 1.0.3
Author: EkAndreas, Flowcom AB
Author URI: http://www.flowcom.se
License: GPLv2
*/

/**
 * Register textdomain
 */
load_plugin_textdomain( 'twitterbox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/*
 * REGISTER WIDGET CLASSES
 */
function twitterbox_register_widget() {
    register_widget( 'TwitterBox_Widget');
}
add_action( 'widgets_init', 'twitterbox_register_widget' );

/**
 * The widget class for TwitterBox
 */
class TwitterBox_Widget extends WP_Widget {

    /**
     * The constructor
     */
    function __construct() {
        parent::WP_Widget('TwitterBox_Widget', 'TwitterBox', array( 'description' => 'TwitterBox' ) );
    }

    function TwitterBox_Widget() {
    }

    /**
     * @param array $instance
     * @return string|void
     */
    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'rpp' => '', 'hash' => '', 'class' => '') );
        $title    = strip_tags($instance['title']);
        $rpp      = strip_tags($instance['rpp']);
        $hash     = strip_tags($instance['hash']);
        $ulli     = strip_tags($instance['ulli']);
        $class     = strip_tags($instance['class']);
        $linktext = $instance['linktext'];
        $linkurl  = $instance['linkurl'];
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'twitterbox'); ?>:
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('hash'); ?>"><?php _e('Hashtags', 'twitterbox'); ?> <em><?php _e('Comma seperated list','twitterbox') ?></em>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('hash'); ?>" name="<?php echo $this->get_field_name('hash'); ?>" value="<?php echo $hash; ?>">
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('rpp'); ?>"><?php _e('Number of tweets', 'twitterbox'); ?>
            <input type="number" class="widefat" min="0" max="20" id="<?php echo $this->get_field_id('rpp'); ?>" name="<?php echo $this->get_field_name('rpp'); ?>" value="<?php echo $rpp; ?>">
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('linktext'); ?>"><?php _e('Linktext', 'twitterbox'); ?>
            <input type="text" class="widefat" min="0" max="20" id="<?php echo $this->get_field_id('linktext'); ?>" name="<?php echo $this->get_field_name('linktext'); ?>" value="<?php echo $linktext; ?>">
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('linkurl'); ?>"><?php _e('Link url', 'twitterbox'); ?>
            <input type="text" class="widefat" min="0" max="20" id="<?php echo $this->get_field_id('linkurl'); ?>" name="<?php echo $this->get_field_name('linkurl'); ?>" value="<?php echo $linkurl; ?>">
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('ulli'); ?>">
            <input type="checkbox" id="<?php echo $this->get_field_id('ulli'); ?>" name="<?php echo $this->get_field_name('ulli'); ?>" value="1" <?php checked($ulli, 1) ?>> <?php _e('Use &lt;ul&gt; &amp; &lt;li&gt; -list', 'twitterbox'); ?>
        </label>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('CSS Class', 'twitterbox'); ?>
            <input type="text" class="widefat" min="0" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" value="<?php echo $class; ?>">
        </label>
    </p>

    <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title']    = strip_tags($new_instance['title']);
        $instance['rpp']      = strip_tags($new_instance['rpp']);
        $instance['linktext'] = strip_tags($new_instance['linktext']);
        $instance['linkurl']  = strip_tags($new_instance['linkurl']);
        $instance['hash']   = strip_tags($new_instance['hash']) ;
        $instance['ulli']   = strip_tags($new_instance['ulli']) ;
        $instance['class']   = strip_tags($new_instance['class']) ;
        return $instance;
    }

    /**
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance) {
        $title      = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
        $rpp        = empty($instance['rpp']) ? '4' : $instance['rpp'];
        $linktext   = $instance['linktext'];
        $linkurl    = $instance['linkurl'];
        $hash       = $instance['hash'];
        $hash       = explode(',', $hash);
        $ulli       = $instance['ulli'];
        $class       = $instance['class'];

        $newhash = array();
        foreach ($hash as $key => $x) {
            $newhash[] = trim($x);
        }
        $hash = implode(' OR ',$newhash);
        $hash = urlencode($hash);

        $result = wp_remote_get('http://search.twitter.com/search.json?q='.$hash.'&rpp='.$rpp);

        if (!is_wp_error($result)) :
            $result = json_decode($result['body']);

            $tweets = $result->results;
            ?>
        <div id="twitterbox-<?php echo $args['id']; ?>" class="widget twitterbox <?php echo $class;?>">
            <?php if (!empty($title)){ ?>
            <h3 class="widget-title"><?php echo $title; ?></h3>
            <?php } ?>
            <div class="tweetlist">
                <?php if ($ulli) echo '<ul class="tweet">'; ?>
                <?php foreach ($tweets as $key => $tweet): ?>
                <?php if ($ulli) echo '<li>'; else echo '<p>'; ?>
                <?php echo $this->linkify($tweet->text) ?>
                <?php echo $this->timeAgo(strtotime($tweet->created_at, time())); ?> <?php _e('by', 'twitterbox'); ?> <a href="https://twitter.com/#!/<?php echo $tweet->from_user ?>"><?php echo $tweet->from_user ?></a>
                <?php if ($ulli) echo '</li>'; else echo '</p>'; ?>
                <?php endforeach ?>
                <?php if ($ulli) echo '</ul>'; ?>
            </div>
            <?php if ($linktext): ?>
            <a href="<?php echo $linkurl ?>" class="more"><?php echo $linktext ?></a>
            <?php endif ?>
        </div>
        <?php
        endif;
    }

    /**
     * @param $timestamp
     * @param string $output
     * @return string
     */
    function timeAgo($timestamp,$output = 'less than a minute ago') {
        $timestamp = time() - $timestamp;
        $units = array(604800=>'week',86400=>'day',3600=>'hour',60=>'minute');
        foreach($units as $seconds => $unit) {
            if($seconds<=$timestamp) {
                $value = floor($timestamp/$seconds);
                $output = 'about '.$value.' '.$unit.($value == 1 ? NULL : 's').' ago';
                break;
            }
        }
        return $output;
    }

    /**
     * @param $string
     * @param bool $twitter
     * @return mixed
     */
    function linkify($string, $twitter = true) {
        // reg exp pattern
        $pattern ="{\\b((http|https?|telnet|gopher|file|wais|ftp) : [\\w/\\#~:.?+=&%@!\\-]+?) (?= [.:?\\-]* (?:[^\\w/\\#~:.?+=&%@!\\-] |$) ) }x";
        $new_string = preg_replace ($pattern, "<a href=\"$1\">$1</a>", $string);
        // convert string URLs to active links $new_string = preg_replace($pattern, "\0", $string);
        if ($twitter) {
            $pattern = '/\@([a-zA-Z0-9_]+)/';
            $replace = '<a href="http://twitter.com/\1">@\1</a>';
            $new_string = preg_replace($pattern, $replace, $new_string);
            $pattern = '/\#([a-zA-Z0-9_]+)/';
            $replace = '<a href="http://twitter.com/search/#\1">#\1</a>';
            $new_string = preg_replace($pattern, $replace, $new_string);
        }
        return $new_string;
    }

}
?>