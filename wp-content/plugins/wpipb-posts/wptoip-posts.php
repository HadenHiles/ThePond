<?php



/*



Plugin Name: WPIPB Posts Shortcode



Description: Plugin for displaying IPBoard 4 posts in WordPress via the [ipbtopics] shortcode



Version: 1.0



Author: Mike Morrison



Author URI: http://www.membersiteacademy.com



License: GPLv3 or later



License URI: http://www.gnu.org/licenses/gpl-3.0.html



Requires at least: 4.0



Tested up to: 4.4.2



*/







defined( 'ABSPATH' ) or die( 'Put the cookie down, now!' );







$options = get_option( 'wpipbf_posts_settings' );







if(!defined('wpipbf_posts_BOARD_PATH')){



    if (isset($options['wpipbf_posts_board_path']) && $options['wpipbf_posts_board_path'] != '') :



        define('wpipbf_posts_BOARD_PATH',$options['wpipbf_posts_board_path']);



    else:



        define('wpipbf_posts_BOARD_PATH','/');



    endif;







}







if(!defined('wpipbf_posts_BOARD_URL')){



    if (isset($options['wpipbf_posts_board_url']) && $options['wpipbf_posts_board_url'] != '') :



        define('wpipbf_posts_BOARD_URL',$options['wpipbf_posts_board_url']);



    else:



        define('wpipbf_posts_BOARD_URL',site_url('/forum/'));



    endif;



}















if ($options['wpipbf_posts_board_path'] && file_exists(wpipbf_posts_BOARD_PATH . '/init.php')) : 



require_once (wpipbf_posts_BOARD_PATH . '/init.php');



endif;











add_action( 'admin_menu', 'wpipbf_posts_add_admin_menu' );



add_action( 'admin_init', 'wpipbf_posts_settings_init' );











function wpipbf_posts_add_admin_menu(  ) { 







    add_options_page( 'WPIPB Posts', 'WPIPB Posts', 'manage_options', 'wordpress_to_ipboard_4_posts', 'wpipbf_posts_options_page' );







}











function wpipbf_posts_settings_init(  ) { 







    register_setting( 'wpipbf_posts_pluginPage', 'wpipbf_posts_settings' );







    add_settings_section(



        'wpipbf_posts_pluginPage_section', 



        __( 'WPIPB Posts Settings', 'wordpress' ), 



        'wpipbf_posts_settings_section_callback', 



        'wpipbf_posts_pluginPage'



    );







    add_settings_field( 



        'wpipbf_posts_board_path', 



        __( 'IPBoard Board Path', 'wordpress' ), 



        'wpipbf_posts_board_path_render', 



        'wpipbf_posts_pluginPage', 



        'wpipbf_posts_pluginPage_section' 



    );



    add_settings_field( 



        'wpipbf_posts_board_url', 



        __( 'IPBoard URL', 'wordpress' ), 



        'wpipbf_posts_board_url_render', 



        'wpipbf_posts_pluginPage', 



        'wpipbf_posts_pluginPage_section' 



    );







}











function wpipbf_posts_board_path_render(  ) { 







    $options = get_option( 'wpipbf_posts_settings' );



    ?>



    <input type='text' name='wpipbf_posts_settings[wpipbf_posts_board_path]' value='<?php echo $options['wpipbf_posts_board_path']; ?>' class="regular-text ltr">



    <p><em>Direct path to your IPBoard installation - i.e: <strong><?php echo $_SERVER['DOCUMENT_ROOT']; ?>/forums/</strong></em></p>



    <?php







}







function wpipbf_posts_board_url_render(  ) { 







    $options = get_option( 'wpipbf_posts_settings' );



    ?>



    <input type='text' name='wpipbf_posts_settings[wpipbf_posts_board_url]' value='<?php echo $options['wpipbf_posts_board_url']; ?>' class="regular-text ltr">



    <p><em>Web URL to your IPBoard installation - i.e: <strong><?php echo site_url(); ?>/forums/</strong></em></p>



    <?php







}







function wpipbf_posts_settings_section_callback(  ) { 







    echo __( 'Configuration for WPIPB Posts', 'wordpress' );







}





function wpipbf_posts_options_page(  ) { 
    ?>

      <style>


    .wpipbf_posts_add {



      border: 1px solid #e5e5e5;



      -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04);



      box-shadow: 0 1px 1px rgba(0,0,0,.04);



      width:75%;



      float:left;



      -moz-box-sizing:border-box;



      box-sizing:border-box;



      padding:20px;





    }



    .wpipbf_posts_right {



      width:25%;



      -moz-box-sizing:border-box;



      box-sizing:border-box;



      padding:20px;



      text-align: center;



      float:right;



    }



    .wpipbf_posts_right img {



      max-width: 100%;



      height:auto;



    }



    </style>







        <div class="wrap">







        <div class="wpipbf_posts_add">



    <form action='options.php' method='post'>



        



        <h1>WPIPB Posts</h1>







        <?php



        settings_fields( 'wpipbf_posts_pluginPage' );



        do_settings_sections( 'wpipbf_posts_pluginPage' );



        submit_button();



        ?>







        



    </form>







    <p>This plugin enables you to show a list of posts from IPBoard in WordPress through the use of a shortcode.</p>



    <p>Simply add <strong>[ipbtopics]</strong> to your page or sidebar (if sidebar shortcodes are enabled).</p>



    <p>To change the limit on the number of posts (8 by default), add 'limit="3"' (replacing '3' with your desired number) to the shortcode: i.e. [ipbtopics limit="3"]</p>











    <div>







        </div>



      



      </div><div class="wpipbf_posts_right">



        <a href="http://www.membersiteacademy.com" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ );?>images/msalogo.png"></a>



      </div></div>



    <?php







}







function wpipbf_posts_list_topics( $atts, $content = null ) {







  extract( shortcode_atts( array(



    'exclude' => '',



    'limit' => '8',



    'forum_link' => 1



  ), $atts ) );







  if ($exclude) :



    $topics=\IPS\Db::i()->select( '*', 'forums_topics', 'forum_id NOT IN (' . $exclude . ')', 'start_date DESC', $limit );



  else:



    $topics=\IPS\Db::i()->select( '*', 'forums_topics', 'forums_posts.new_topic=1', 'start_date DESC', $limit );

	$topics->join( 'forums_posts', 'forums_posts.topic_id=forums_topics.tid', 'LEFT' );

	 

  endif;





	  

    if(count($topics)>0){



        $output.='<ul class="wpipb_topic_list">';



        foreach($topics as $topic){

         

            $output.='<li><h4> <a href="' . wpipbf_posts_BOARD_URL . 'index.php?/topic/' . $topic['tid'] . '-' . $topic['title_seo'] . '">' . $topic['title'] . ' </a></h4>

			'.wp_trim_words($topic['post'] , 10, "...") .'<p class="author">Posted by <a href="'.wpipbf_posts_BOARD_URL . 'index.php?/profile/'. $topic['starter_id'] . '-' . $topic['starter_name'] .'">'.$topic['starter_name'].' </a></p>

            <a class="BTN" href="' . wpipbf_posts_BOARD_URL . 'index.php?/topic/' . $topic['tid'] . '-' . $topic['title_seo'] . '">  VIEW POST</a>

			

            </li>';



        }



    }



    $output.='</ul>';



    if ($forum_link) :



        $output.='<div class="wpipb_visit_forum"><a href="' . wpipbf_posts_BOARD_URL . '">Click here to visit the forum</a></div>';



    endif;



    return $output;



}











$options = get_option( 'wpipbf_posts_settings' );







if ($options['wpipbf_posts_board_path'] && file_exists($options['wpipbf_posts_board_path'] . '/init.php')) : 







    add_shortcode("ipbtopics", "wpipbf_posts_list_topics");







else:







    function wpipbf_posts_admin_error_notice() {



        $class = "update-nag";



        $message = "<strong>WPIPB Posts</strong> - you need to configure your board URL and board path before the posts shortcode will work. If you have completed the settings and still see this notice it means that you have used an incorrect value for the IPBoard path option.";



            echo"<div class=\"$class\"> <p>$message</p></div>"; 



    }



    add_action( 'admin_notices', 'wpipbf_posts_admin_error_notice' ); 







endif;