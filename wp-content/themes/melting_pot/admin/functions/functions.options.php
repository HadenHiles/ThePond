<?php

add_action('init','of_options');

if (!function_exists('of_options'))
{
      function of_options()
      {
           global $smof_data;
		  
            //Access the WordPress Categories via an Array
            $of_categories          = array();
            $of_categories_obj      = get_categories('hide_empty=0');
            foreach ($of_categories_obj as $of_cat) {
                $of_categories[$of_cat->cat_ID] = $of_cat->cat_name;}
            $categories_tmp   = array_unshift($of_categories, "Select a category:");

            //Access the WordPress Pages via an Array
            $of_pages               = array();
            $of_pages_obj           = get_pages('sort_column=post_parent,menu_order');
            foreach ($of_pages_obj as $of_page) {
                $of_pages[$of_page->ID] = $of_page->post_name; }
            $of_pages_tmp           = array_unshift($of_pages, "Select a page:");
			
            //Testing
            $of_options_select      = array("one"=>"One","two"=> "Two","three"=>"Three","four"=>"Four");
			$of_options_select_layout      = array("one"=>"Layout One","two"=> "Layout Two" , "three"=> "Layout Three");
            $of_options_radio       = array("one" => "One","two" => "Two","three" => "Three","four" => "Four","five" => "Five");

         
            // Social Icon default order
            $of_options_social_links_ordering = array
            (
                  "default" => array (
                        'facebook' => 'Facebook',
                        'flickr' => 'Flickr',
                        'rss' => 'RSS',
                        'twitter' => 'Twitter',
                        'vimeo' => 'Vimeo',
                        'youtube' => 'Youtube',
                        'instagram' => 'Instagram',
                        'pinterest' => 'Pinerest',
                        'tumblr' => 'Tumblr',
                        'google' => 'Googleplus',
                        'dribbble' => 'Dribble',
                        'digg' => 'Digg',
                        'linkedin' => 'LinkedIn',
                        'blogger' => 'Blogger',
                        'skype' => 'Skype',
                        'forrst' => 'Forrst',
                        'myspace' => 'Myspace',
                        'deviantart' => 'Deviantart',
                        'yahoo' => 'Yahoo',
                        'reddit' => 'Reddit',
                        'paypal' => 'Paypal',
                        'dropbox' => 'Dropbox',
                        'soundcloud' => 'Soundcloud',
                        'vk' => 'VK',
                  ),
                  "custom" => array (
                  ),
            );
           

            //Stylesheets Reader
            $alt_stylesheet_path = 'LAYOUT_PATH';
            $alt_stylesheets = array();

            if ( is_dir($alt_stylesheet_path) )
            {
                if ($alt_stylesheet_dir = opendir($alt_stylesheet_path) )
                {
                    while ( ($alt_stylesheet_file = readdir($alt_stylesheet_dir)) !== false )
                    {
                        if(stristr($alt_stylesheet_file, ".css") !== false)
                        {
                            $alt_stylesheets[] = $alt_stylesheet_file;
                        }
                    }
                }
            }


            //Background Images Reader
            $bg_images_path = get_stylesheet_directory(). '/images/bg/'; // change this to where you store your bg images
            $bg_images_url = get_template_directory_uri().'/images/bg/'; // change this to where you store your bg images
            $bg_images = array();

            if ( is_dir($bg_images_path) ) {
                if ($bg_images_dir = opendir($bg_images_path) ) {
                    while ( ($bg_images_file = readdir($bg_images_dir)) !== false ) {
                        if(stristr($bg_images_file, ".png") !== false || stristr($bg_images_file, ".jpg") !== false) {
                              natsort($bg_images); //Sorts the array into a natural order
                            $bg_images[] = $bg_images_url . $bg_images_file;
                        }
                    }
                }
            }


            /*-----------------------------------------------------------------------------------*/
            /* TO DO: Add options/functions that use these */
            /*-----------------------------------------------------------------------------------*/

            //More Options
            $uploads_arr            = wp_upload_dir();
            $all_uploads_path       = $uploads_arr['path'];
            $all_uploads            = get_option('of_uploads');
            $other_entries          = array("Select a number:","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19");
            $body_repeat            = array("no-repeat","repeat-x","repeat-y","repeat");
            $body_pos               = array("top left","top center","top right","center left","center center","center right","bottom left","bottom center","bottom right");

            // Image Alignment radio box
            $of_options_thumb_align = array("alignleft" => "Left","alignright" => "Right","aligncenter" => "Center");

            // Image Links to Options
            $of_options_image_link_to = array("image" => "The Image","post" => "The Post");


			/*-----------------------------------------------------------------------------------*/
			/* The Options Array */
			/*-----------------------------------------------------------------------------------*/

			// Set the Options Array
			global $of_options;
			$of_options = array();

  

        $of_options[] = array( "name" => "General",
            "type" => "heading");
		
        $of_options[] = array( "name" => "General Options",
        		"desc" => "",
        		"id" => "general",
        		"std" => "<h3 style='margin: 0;'>General Options</h3>",
        		"icon" => true,
        		"type" => "info");
        
        $of_options[] = array( "name" => "Strapline",
            "desc" => "markup can be used",
            "id" => "strapline",
            "std" => "",
            "icon" => true,
            "type" => "textarea");

        $of_options[] = array( "name" => "Phone Number",
            "desc" => "This box to use the phone number.",
            "id" => "phone_number",
            "std" => "",
            "type" => "textarea");
			
		
		 $of_options[] = array( "name" => "Don't allow theme updates?",
            "desc" => "Check to stop theme update feature.",
            "id" => "stop_theme_update",
            "std" => "",
            "mod" => "",
            "type" => "checkbox", 
			);	
        
		
		 $of_options[] = array( "name" => "Theme Styles",
            "type" => "heading");
		
        $of_options[] = array( "name" => "Theme Styles And Fonts",
        		"desc" => "",
        		"id" => "codes",
        		"std" => "<h3 style='margin: 0;'>Theme Styles And Fonts</h3>",
        		"icon" => true,
        		"type" => "info");
		
        $of_options[] = array( "name" => "Goggle Font",
        		"desc" => "This box to use for google font.",
        		"id" => "google_font_url",
        		"std" => "",
       			"type" => "textarea");

		 $of_options[] = array( "name" => "Goggle Font Secondy",
        		"desc" => "This box to use for google font.",
        		"id" => "google_font_url_sec",
        		"std" => "",
       			"type" => "textarea");
        
		  
		   $of_options[] = array( "name" => "Font Awesome",
        		"desc" => "This box to use for font awesome.",
        		"id" => "font_awesome_url",
        		"std" => "",
       			"type" => "textarea");
				
			 $of_options[] = array( "name" => "Background Image 404",
            "desc" => "Select an image file for your 404 page.",
            "id" => "404_image",
            "std" => "",
            "mod" => "",
            "type" => "media");	
        
		  
		  
		 $of_options[] = array( "name" => "Tracking Codes",
            "type" => "heading");
		
        $of_options[] = array( "name" => "Tracking Codes",
        		"desc" => "",
        		"id" => "codes",
        		"std" => "<h3 style='margin: 0;'>Tracking Codes</h3><p>Add your marketing pixels and codes here.</p>",
        		"icon" => true,
        		"type" => "info");
        
        $of_options[] = array( "name" => "Google Tag Manager (Script)",
            "desc" => "The first part of your Google Tag Manager Code - this will be entered into the head tag of your website",
            "id" => "googletagmanager",
            "std" => "",
            "icon" => true,
            "type" => "textarea");

        $of_options[] = array( "name" => "Google Tag Manager (No Script)",
            "desc" => "The second part of your Google Tag Manager Code - this will be entered into the body tag of your website",
            "id" => "googletagmanagernoscript",
            "std" => "",
            "icon" => true,
            "type" => "textarea");
		  
			  
	   $of_options[] = array( "name" => "Header Scripts",
		"desc" => "Extra scripts to insert into the head tag on your website. E.g. Facebook Pixel.",
		"id" => "head_script",
		"std" => "",
		"icon" => true,
		"type" => "textarea");
		  
		  
		  $of_options[] = array( "name" => "Footer Scripts",
		"desc" => "Extra scripts to insert into the footer tag on your website. E.g. Live chat codes.",
		"id" => "extrafooterscripts",
		"std" => "",
		"icon" => true,
		"type" => "textarea");
		  
		  
	 				
        
		  
		  
		  
		  

       			
        $of_options[] = array( "name" => "Logo",
            "type" => "heading");
		
        $of_options[] = array( "name" => "Logo Options",
        		"desc" => "",
        		"id" => "logo_options",
        		"std" => "<h3 style='margin: 0;'>Logo Options</h3>",
        		"icon" => true,
        		"type" => "info");
        
       
        $of_options[] = array( "name" => "Header Logo",
            "desc" => "Select an image file for your logo.",
            "id" => "header_logo",
            "std" => get_bloginfo('template_directory')."/images/logo.png",
            "mod" => "",
            "type" => "media");
		
        $of_options[] = array( "name" => "Footer Logo",
        		"desc" => "Select an image file for your footer logo.",
        		"id" => "footer_logo",
        		"std" => get_bloginfo('template_directory')."/images/logo.png",
        		"mod" => "",
        		"type" => "media");
        
  
        
       
     
		  
		  
		  
        
       
		
        $of_options[] = array( "name" => "Social",
            "type" => "heading");

        $of_options[] = array( "name" => "Social Link",
            "desc" => "",
            "id" => "header_info",
            "std" => "<h3 style='margin: 0;'>Social Options</h3>",
            "icon" => true,
            "type" => "info");

		$of_options[] = array( "name" =>  "Facebook Link.",
            "desc" => "Add your facebook link .",
            "id" => "facebook_link",
            "std" => "",
            "type" => "text");

		$of_options[] = array( "name" =>  "Twitter Link.",
				"desc" => "Add your Twitter link .",
				"id" => "twitter_link",
				"std" => "",
				"type" => "text");
		
		$of_options[] = array( "name" =>  "Youtube Link.",
				"desc" => "Add your youtube link .",
				"id" => "youtube_link",
				"std" => "",
				"type" => "text");
		
		$of_options[] = array( "name" =>  "Google+ Link.",
				"desc" => "Add your Google+ link .",
				"id" => "google_link",
				"std" => "",
				"type" => "text");
		  
		  
		 $of_options[] = array( "name" =>  "LinkedIn Link.",
				"desc" => "Add your LinkedIn link .",
				"id" => "linkedin_link",
				"std" => "",
				"type" => "text");
		  
		  
		   $of_options[] = array( "name" =>  "Instagram Link.",
				"desc" => "Add your Instagram link .",
				"id" => "insta_link",
				"std" => "",
				"type" => "text");
		  
		  
		  $of_options[] = array( "name" =>  "New Social Link.",
				"desc" => "Add your Social link .",
				"id" => "social_new_link",
				"std" => "",
				"type" => "text");
				
			$of_options[] = array( "name" => "Feature",
            "type" => "heading"); 
		  
			  $of_options[] = array( "name" => "Feature Options",
				"desc" => "",
				"id" => "header_info",
				"std" => "<h3 style='margin: 0;'>Feature Options</h3>",
				"icon" => true,
				"type" => "info");
				
			 $of_options[] = array( "name" => "Is Membership Site",
				"desc" => "Select to enable member template and feature.",
				"id" => "is_member_site",
				"std" => "",
				"mod" => "",
				"type" => "checkbox", 
				);
			  
			   $of_options[] = array( "name" => "Enable Podcast Feature",
				"desc" => "Select to enable Podcast feature.",
				"id" => "enable_podcast",
				"std" => "",
				"mod" => "",
				"type" => "checkbox", 
				);
				
				 $of_options[] = array( "name" => "Enable Member Directory",
					"desc" => "Select to enable member directory feature.",
					"id" => "enable_member_directory",
					"std" => "",
					"mod" => "",
					"type" => "checkbox", 
					);
				
			 $of_options[] = array( "name" => "Enable Case Study",
				"desc" => "Select to enable case study feature.",
				"id" => "enable_case_study",
				"std" => "",
				"mod" => "",
				"type" => "checkbox", 
				);			
			
			 $of_options[] = array( "name" => "Enable Ask Question Feature",
				"desc" => "Select to enable ask question feature.",
				"id" => "enable_ask_question",
				"std" => "",
				"mod" => "",
				"type" => "checkbox", 
				);		
		 	
	 

		  $of_options[] = array( "name" => "Member Page",
            "type" => "heading"); 
		  
		  $of_options[] = array( "name" => "Member Page Options",
            "desc" => "",
            "id" => "header_info",
            "std" => "<h3 style='margin: 0;'>Member Page Options</h3>",
            "icon" => true,
            "type" => "info");
		
		
			
	 		
		  $of_options[] = array( "name" => "Dashboard Logo",
            "desc" => "Select an image file for member dashboard logo.",
            "id" => "member_dashboard_logo",
            "std" => "",
            "mod" => "",
            "type" => "media");
			
		 $of_options[] = array( "name" => "Member Home Page URL",
            "desc" => "add your member home page url .",
            "id" => "member_homepage_url",
            "std" => "",
            "mod" => "",
            "type" => "text");	
		  
		 
			
		
			
		 $of_options[] = array( "name" => "Rename Content Library Text",
            "desc" => "Rename Content Library Text.",
            "id" => "rename_content_library",
            "std" => "Content Library",
            "mod" => "",
            "type" => "text", 
			);	
		 
		 
		/*
		$pageTemplates =  array(
			"members-templates/member-account.php" => 'Member Account',
			'members-templates/member-dashboard.php' => 'Members Dashboard',
			'members-templates/member_login.php'   =>	'Member Login',
			'members-templates/member_noaccess.php' =>	'Member Restricted Access',
			'members-templates/member_register.php' =>	'Member Register',
			'members-templates/member_support.php' =>	'Members Support',
			'members-templates/member_welcome.php' =>	'Members Welcome'
		
		);
		*/
	 	
		 $of_options[] = array( "name" => "Course Page",
            "type" => "heading"); 
		  
		  $of_options[] = array( "name" => "Course Page Options",
            "desc" => "",
            "id" => "header_info",
            "std" => "<h3 style='margin: 0;'>Course Page Options</h3>",
            "icon" => true,
            "type" => "info");
			
			
		 
			$of_options[] = array( "name" => "Page Layout Column",
            "desc" => "Select no of column for course page.",
            "id" => "course_page_column",
            "std" => "",
            "mod" => "",
            "type" => "select",
			"options"=> $of_options_select
			);
			
			$of_options[] = array( "name" => "Single Page Layout",
            "desc" => "Change single course page layout",
            "id" => "course_page_layout",
            "std" => "",
            "mod" => "",
            "type" => "select",
			"options"=> $of_options_select_layout
			);
			
		 	

			 $of_options[] = array( "name" => "Directory",
            "type" => "heading"); 
		  
			  $of_options[] = array( "name" => "Directory Options",
				"desc" => "",
				"id" => "header_info",
				"std" => "<h3 style='margin: 0;'>Directory Options</h3>",
				"icon" => true,
				"type" => "info");
				
			 $of_options[] = array( "name" => "Custom Title",
				"desc" => "Custom title for directory page.",
				"id" => "directry_custom_title",
				"std" => "",
				"mod" => "",
				"type" => "text", 
			);		
			
			 $of_options[] = array( "name" => "Custom Sub Title",
				"desc" => "Custom sub title for directory page.",
				"id" => "directry_custom_sub_title",
				"std" => "",
				"mod" => "",
				"type" => "text", 
			);		
				
				
			 $of_options[] = array( "name" => "Ask Page",
            "type" => "heading"); 
		  
			  $of_options[] = array( "name" => "Ask Page Options",
				"desc" => "",
				"id" => "header_info",
				"std" => "<h3 style='margin: 0;'>Ask Page Options</h3>",
				"icon" => true,
				"type" => "info");	
				
			  $of_options[] = array( "name" => "Form Id",
				"desc" => "Gravity from id.",
				"id" => "gravity_form_id",
				"std" => "",
				"mod" => "",
				"type" => "text", 
			);	
          
          			 $of_options[] = array( "name" => "Podcasts",
            "type" => "heading"); 
		  
		 $of_options[] = array( "name" => "Subscription Link 1",
				"desc" => "Link to podcast subscription page 1.",
				"id" => "podcast_sub_link_one",
				"std" => "",
				"mod" => "",
				"type" => "text", 
		 );		
          
        $of_options[] = array( "name" => "Subscription Image 1",
            "desc" => "Select an image file for subscription 1.",
            "id" => "podcast_sub_image_one",
            "std" => "",
            "mod" => "",
            "type" => "media",
        );  
        
          
        $of_options[] = array( "name" => "Subscription Link 2",
				"desc" => "Link to podcast subscription page 2.",
				"id" => "podcast_sub_link_two",
				"std" => "",
				"mod" => "",
				"type" => "text", 
		);	
          
        $of_options[] = array( "name" => "Subscription Image 2",
            "desc" => "Select an image file for subscription 2.",
            "id" => "podcast_sub_image_two",
            "std" => "",
            "mod" => "",
            "type" => "media",
        );           
          
			/*
			$of_options[] = array( "name" => "Show on page template",
            "desc" => "check where you want to show this sidebar",
            "id" => "pages_reason_to_join",
            "std" => "",
            "mod" => "",
            "type" => "multicheck",
			"options" => $pageTemplates,
			);
			
			$of_options[] = array( "name" => "Sidebar Promo",
            "desc" => "add text for Sidebar Promo.",
            "id" => "sidebar_promo",
            "std" => "",
            "mod" => "",
            "type" => "textarea");
			
			$of_options[] = array( "name" => "Show on page template",
            "desc" => "check where you want to show this sidebar",
            "id" => "pages_sidebar_promo",
            "std" => "",
            "mod" => "",
            "type" => "multicheck",
			"options" => $pageTemplates,
			);
			
			$of_options[] = array( "name" => "Sidebar Support",
            "desc" => "add text for Sidebar Support.",
            "id" => "sidebar_support",
            "std" => "",
            "mod" => "",
            "type" => "textarea");
        
		 $of_options[] = array( "name" => "Show on page template",
            "desc" => "check where you want to show this sidebar",
            "id" => "pages_sidebar_support",
            "std" => "",
            "mod" => "",
            "type" => "multicheck",
			"options" => $pageTemplates,
			);*/
            
      }//End function: of_options()
}//End chack if function exists: of_options()
?>