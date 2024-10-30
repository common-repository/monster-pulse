<?php /*
Plugin Name: Monster Пульс
Plugin URI: https://moshikov.co/
Description: Автоматически генерирует RSS-ленту для рекомендательной системы Пульс от Mail.Ru за последние 7 дней. Есть возможность исключения записей.
Version: 1.2.2
Author: Vladislav Moshikov
Author URI: http://moshikov.co/
Copyright: Vladislav Moshikov
Text Domain: monster-pulse
License: GPL3
*/

// Инициализации
add_action('init', function(){
    add_feed('pulse', 'monsterpulse_rss');
});
add_action('admin_menu', function(){
    add_options_page('Monster Пульс', 'Monster Пульс', 'manage_options', 'functions.php', 'monsterpulse_options_page');
});

function monsterpulse_options_page() {
    $purl = plugins_url('', __FILE__);
    
    if (isset($_POST['submit'])) {
        //  Проверяем безопасность
    if ( ! wp_verify_nonce( $_POST['monsterpulse_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
       wp_die(__( 'Cheatin&#8217; uh?' ));
    }
    // Если все впорядке
    
        $monsterpulse_options = get_option('monsterpulse_options');
        
        if (!preg_match('/[^A-Za-z0-9]/', $_POST['mpulse_title']))  {
            $monsterpulse_options['mpulse_title'] = sanitize_text_field($_POST['mpulse_title']);
            update_option('monsterpulse_options', $monsterpulse_options);
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
        $monsterpulse_options['mpulse_title'] = sanitize_text_field($_POST['mpulse_title']);
        $monsterpulse_options['mpulse_url'] = sanitize_text_field($_POST['mpulse_url']);
        $monsterpulse_options['mpulse_post_age'] = sanitize_text_field($_POST['mpulse_post_age']);
        $monsterpulse_options['mpulse_url_AMP'] = sanitize_text_field($_POST['mpulse_url_AMP']);
        $monsterpulse_options['mpulse_check_AMP'] = $_POST['mpulse_check_AMP'];
        // $monsterpulse_options['yzdescription'] = sanitize_text_field($_POST['yzdescription']);
        
        update_option('monsterpulse_options', $monsterpulse_options);
    }
    $monsterpulse_options = get_option('monsterpulse_options');
    ?>
    <?php if (!empty($_POST) ) :
    if ( ! wp_verify_nonce( $_POST['monsterpulse_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
       wp_die(__( 'Cheatin&#8217; uh?' ));
    }
    ?>
    <div id="message" class="updated fade"><p><strong>Настройки сохранены</strong></p></div>
    <?php endif; ?>
    
    <div class="wrap">
    <h2>Настройки "Пульса"</h2>
    
    <div class="metabox-holder" id="poststuff">
    <div class="meta-box-sortables">
    
    <form action="" method="post">
    
    <div class="postbox">
    
        <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e("Настройки", 'rss-for-yandex-zen'); ?></span></h3>
        <div class="inside" style="display: block;">
    
            <table class="form-table">
            
                <tr>
                    <th>Заголовок</th>
                    <td>
                        <input type="text" name="mpulse_title" size="40" value="<?php echo stripslashes($monsterpulse_options['mpulse_title']); ?>" />
                        <br /><small>Название ресурса </small>
                    </td>
                </tr>
                <tr>
                    <th>Ссылка</th>
                    <td>
                        <input type="text" name="mpulse_url" size="40" value="<?php echo stripslashes($monsterpulse_options['mpulse_url']); ?>" />
                        <br /><small>Адрес сайта (без http и www) <kbd>moshikov.co</kbd></small>
                   </td>
                </tr>
                <tr>
                    <th>Возраст постов</th>
                    <td>
                        <input type="text" name="mpulse_post_age" size="40" value="<?php echo stripslashes($monsterpulse_options['mpulse_post_age']); ?>" />
                        <br /><small>Посты за какое клоичество дней будут включены в фид</small>
                   </td>
                </tr>
                <tr>
                <?php echo $monsterpulse_options['mpulse_check_AMP']; ?>
                    <th>Поддержка AMP</th>
                    <td>
                        <label for="mpulse_check_AMP"><input type="checkbox" name="mpulse_check_AMP" value="enabled" <?php if ($monsterpulse_options['mpulse_check_AMP'] == 'enabled') echo 'checked="checked"'; ?> />Включить поддержку AMP страниц</label>
                        <br>
                        <small>Добавляет в ленту RSS адрес нативную AMP-версии материала. example.com/post_url/amp/</small>
                    </td>
                </tr>
                <tr>
                    <th>Формат AMP</th>
                    <td>
                    <input type="text" name="mpulse_url_AMP" size="40" value="<?php echo stripslashes($monsterpulse_options['mpulse_url_AMP']); ?>" />
                        <br /><small>Постройте ссылку до вашей AMP-страницы: <kbd>amp.#site_url#/#post_url#</kbd><br>
                        <kbd>#site_url#</kbd> – Адрес сайта<br>
                        <kbd>#post_url#</kbd> – Материал</small>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="submit" name="submit" class="button button-primary" value="Сохранить настройки" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php wp_nonce_field( plugin_basename(__FILE__), 'monsterpulse_nonce'); ?>
    </form>
    </div>
    </div>
    <?php 
}

function monsterpulse_meta_box(){
    $monsterpulse_options = get_option('monsterpulse_options');  
    add_meta_box('monsterpulse_meta_box', 'Mail.ru Пульс', 'monsterpulse_meta_callback', 'post', 'normal', 'high');
}
add_action( 'add_meta_boxes', 'monsterpulse_meta_box' );

function monsterpulse_save_metabox($post_id){ 
    global $post;
    
    if ( ! isset( $_POST['monsterpulse_meta_nonce'] ) ) 
        return $post_id;
 
    if ( ! wp_verify_nonce($_POST['monsterpulse_meta_nonce'], plugin_basename(__FILE__) ) )
		return $post_id;
    
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;
        
    if(isset($_POST["mpulseenable"])){
        $mpulseenable = 'yes';
        update_post_meta($post->ID, 'mpulseenable_meta_value', $mpulseenable);
    } else {
        $mpulseenable = 'no';
        update_post_meta($post->ID, 'mpulseenable_meta_value', $mpulseenable);
    }     
    
}
add_action('save_post', 'monsterpulse_save_metabox');

function monsterpulse_meta_callback(){
    global $post;
	wp_nonce_field( plugin_basename(__FILE__), 'monsterpulse_meta_nonce' );
    
    $monsterpulse_options = get_option('monsterpulse_options');
    $mpulse_AMP_enabled = $monsterpulse_options['mpulse_check_AMP'];

    $mpulseenable = get_post_meta($post->ID, 'mpulseenable_meta_value', true); 
    if (!$mpulseenable) {$mpulseenable = "no";}   
    ?>   
    
    <p style="margin:5px!important;">
    <label for="mpulseenable"><input type="checkbox" value="enabled" name="mpulseenable" id="mpulseenable" <?php if ($mpulseenable == 'excluded') echo "checked='checked'"; ?> /><?php _e("Исключить эту запись из RSS", "rss-for-yandex-zen"); ?></label>
    </p>
    <p>AMP версия: <kbd><?php if($mpulse_AMP_enabled == 'enabled') { echo "Включена"; } else { echo "Отключена"; } ?></kbd></p>
    
<?php }


function monsterpulse_rss(){
    $monsterpulse_options = get_option('monsterpulse_options');  
    
    $mpulse_title = $monsterpulse_options['mpulse_title'];
    $mpulse_protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    $mpulse_url = $monsterpulse_options['mpulse_url'];
    $mpulse_post_age = $monsterpulse_options['mpulse_post_age'];
    $mpulse_AMP_enabled = $monsterpulse_options['mpulse_check_AMP'];
    $mpulse_url_AMP = $monsterpulse_options['mpulse_url_AMP'];
    $mpulse_filter_site_url = str_replace("#site_url#", $mpulse_url, $mpulse_url_AMP);

    $args = array(
        'ignore_sticky_posts' => false,
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR', 
            array(
                'key' => 'mpulseenable_meta_value', 
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => 'mpulseenable_meta_value', 
                'value' => 'excluded', 'compare' => '!=',
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC',
        'date_query' => array(
            array(
                'after' => $mpulse_post_age.' day ago'
            )
        )
    );

    $query = new WP_Query( $args );
    
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'.PHP_EOL;
    ?>
    <rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:georss="http://www.georss.org/georss">
    <channel>
        <title><?php echo $mpulse_title; ?></title>
        <link><?php bloginfo("url"); ?></link>
        <language>ru</language>
        <generator>RSS для рекомендательной системы Mail.Ru – Пульс (https://wordpress.org/plugins/monster-pulse/)</generator>
        <?php while($query->have_posts()) : $query->the_post(); 
        $mpulse_permalink = substr(str_replace(home_url().'/', '', get_permalink()), 0, -1);
        $mpulse_filter_url_AMP = str_replace("#post_url#", $mpulse_permalink, $mpulse_filter_site_url);
        ?>
        <item>
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <?php if($mpulse_AMP_enabled == 'enabled') { ?>
            <amplink><?php echo $mpulse_protocol.$mpulse_filter_url_AMP; ?></amplink>
            <?php } ?>
            <guid><?php the_guid(); ?></guid>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <?php
            if (has_post_thumbnail(get_the_ID() )) {
                $mpulse_gallery = get_attached_media('image', $post);
                foreach($mpulse_gallery as $mpulse_item_image) {
                    echo '<enclosure url="'.$mpulse_item_image->guid.'" type="'.monsterpulse_mimetype($mpulse_item_image->guid).'" />';
                }
            }      
            ?>
            <description><![CDATA[<?php echo monsterpulse_description(); ?>]]></description>
        </item>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>
    <?php wp_reset_query(); ?>
    </channel>
    </rss>
    <?php }

    function monsterpulse_mimetype($file) {
        $mime_type = array(
            "bmp"			=>	"image/bmp",
            "gif"			=>	"image/gif",
            "ico"			=>	"image/x-icon",
            "jpeg"			=>	"image/jpeg",
            "jpg"			=>	"image/jpeg",
            "png"			=>	"image/png",
            "psd"			=>	"image/vnd.adobe.photoshop",
            "svg"			=>	"image/svg+xml",
            "tiff"			=>	"image/tiff",
            "webp"			=>	"image/webp",
        );
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($mime_type[$extension])) {
            return $mime_type[$extension];
        } else {
            return "Unknown file type";
        }
    }

    function monsterpulse_description() {
        $content = get_the_excerpt();
        $content = apply_filters('monsterpulse_the_excerpt', $content);
        $content = apply_filters('convert_chars', $content);
        $content = apply_filters('ent2ncr', $content, 8);
        return $content;
    }

?>