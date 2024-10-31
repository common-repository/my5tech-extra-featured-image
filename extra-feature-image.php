<?php
/**
Plugin Name: Extra Feature Image
Description: You need a extra featured image for posts, pages and/or custom post types? Then this plugin is for you!. Use shortcode to display on posts, pages and/or custom post types [my5tech_extra_image].
Author: Infoseek Team
Author URI: http://infoseeksoftwaresystems.com/
Version: 1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if( !class_exists( 'My5techExtraImage' ) ) {
	
	class My5techExtraImage {
		/**
		 * Constructor
		 */
		public function __construct() {

			// Enqueue Scripts
			add_action('admin_enqueue_scripts', array( $this, 'my5tech_media_lib_uploader_enqueue'));

			// Init Plugin
			add_action( 'init', array( $this, 'my5techInit' ) );
		}  
		
		/**
		 * Enqueue Script
		 */
		public function my5tech_media_lib_uploader_enqueue() {
			wp_enqueue_media();
			wp_register_script( 'media-lib-uploader-js', plugins_url( 'js/media-lib-uploader.js' , __FILE__ ), array('jquery') );
			wp_enqueue_script( 'media-lib-uploader-js' );
		}
		
		/**
		 * Init the Plugin
		 */
		public function my5techInit() {

			// The current theme has to support post thumbnails
			if( !current_theme_supports( 'post-thumbnails' ) ) {
				add_theme_support( 'post-thumbnails' );
			}
			
			// Add MetaBox
			add_action( 'add_meta_boxes', array( $this, 'add_my5tech_meta_box' ));
			
			// Save Extra image 
			add_action("save_post", array( $this, "my5tech_set_featured_image"),10, 3);
			
			// Register our own Ajax to remove feature image
			add_action( 'wp_ajax_my5tech_remove_featured_image', array( $this, 'my5tech_remove_featured_image' ) );
			
			// Add a shortcode to display featured image
			add_shortcode("my5tech_extra_image", array( $this, "my5tech_extra_image_func"));
		}
		
		/**
		 * Created Custom MetaBox
		*/
		public function add_my5tech_meta_box() {
			$post_types = get_post_types();
			add_meta_box("my5tech-meta-box", "Extra Feature Image", array( $this ,'my5tech_meta_box_area'), $post_types, "side", "low");
		}	
		public function my5tech_meta_box_area($post)
		{
			$my5tech_photo_id = get_post_meta( $post->ID, 'my5tech_extra_image_2', true );

			wp_nonce_field(basename(__FILE__), "my5tech-meta-box-nonce");

			if( $my5tech_photo_id ) {
				$link_title = wp_get_attachment_image( $my5tech_photo_id, 'full', false, array( 'style' => 'width:100%;height:auto;', ) );
				$hide_remove_button = '';
			}
			else {
				$my5tech_photo_id = -1;
				$link_title = 'Set featured image 2';
				$hide_remove_button = 'display: none;';
			}
			?>
			<input type="hidden" id="my5tech_extra_image_2" name="my5tech_extra_image_2" value="<?php echo $my5tech_photo_id; ?>">
			<p class="hide-if-no-js"><a href="javascript:void(0)" id="my5-box-img1" data-nonce="<?php echo $nonce; ?>" style="display: inline-block;" data-id="<?php echo $post->ID;?>"><?php echo $link_title; ?></a></p>
			<p class="hide-if-no-js howto2" style="<?php echo $hide_remove_button;?>">Click the image to edit or update</p>
			
			<p class="hide-if-no-js hide-if-no-image" style="<?php echo $hide_remove_button;?>"><a href="javascript:void(0)" id="my5-box-img2" onclick="my5techRemoveImage(<?php echo $post->ID;?>)">Remove featured image 2</a></p>  
			<script type="text/javascript">
			 function my5techRemoveImage(fea_id){
				var data = {
				   action: 'my5tech_remove_featured_image',
				   fea_id: fea_id
				}
			    jQuery.post( ajaxurl, data, function( response ) {
			   
					if(response.success == true){
					 //console.log(response);
					 jQuery('#my5-box-img1').html('Set featured image 2');
					 jQuery('.howto2').hide();
					 jQuery('.hide-if-no-image').hide();
				    }else{
						console.log(response);
					}
				});
			 }
			</script>
			<?php
		}
		
		/**
		 * Save Feature image 
		*/
		public function my5tech_set_featured_image($post_id, $post, $update)
		{
			
			if (!isset($_POST["my5tech-meta-box-nonce"]) || !wp_verify_nonce($_POST["my5tech-meta-box-nonce"], basename(__FILE__)))
				return $post_id;

			if(!current_user_can("edit_post", $post_id))
				return $post_id;

			if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
				return $post_id;

			if(isset($_POST["my5tech_extra_image_2"]))
			{
				update_post_meta($post_id, "my5tech_extra_image_2", $_POST["my5tech_extra_image_2"]);
			}			
		}
		
		/**
		 * Remove Feature image 
		*/
		public function my5tech_remove_featured_image()
		{
			// Instantiate WP_Ajax_Response
			$response = new WP_Ajax_Response;
			
			if( current_user_can( 'manage_options' )) {
				$postid = $_REQUEST['fea_id'];
				if(isset($_REQUEST['fea_id'])){
					$isDeleted = delete_post_meta( $_REQUEST['fea_id'], 'my5tech_extra_image_2');
					if($isDeleted)
						$str = 'success';
					else
						$str = 'Fail';
				}else{
					$str = 'Fail';
				}
				wp_send_json_success($str);
			}else{
				wp_send_json_error();
			}
			wp_die();
		}
		
		/**
		 * Short code[my5tech_extra_image] to display extra featured image
		*/
		public function my5tech_extra_image_func()
		{
			ob_start();
			global $post;
			$my5tech_photo_id = get_post_meta( $post->ID, 'my5tech_extra_image_2', true );
			if( $my5tech_photo_id ) {
				echo wp_get_attachment_image( $my5tech_photo_id, 'full', false, array( 'style' => 'width:100%;height:auto;', ) );
			}
			return ob_get_clean();
		}
	}
	/**
	* Init class
	*/
	new My5techExtraImage();
}
