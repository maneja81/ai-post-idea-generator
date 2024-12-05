<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ai_post_idea_generator_autoload
 *
 * This class is responsible for managing the initialization and
 * singleton instance of the AI Post Idea Generator plugin. It ensures
 * that only one instance of the class is created and provides a centralized
 * point for initialization logic.
 */
class ai_post_idea_generator_autoload {


	/**
	 * Holds the singleton instance of the class.
	 *
	 * @var ai_post_idea_generator_autoload|null
	 */
	private static $instance;

	/**
	 * @var mixed $helpers This property holds helper functions or classes.
	 */
	public $helpers;

	/**
	 * Gets the singleton instance of the class.
	 *
	 * If an instance of the class does not already exist, it creates one.
	 *
	 * @return ai_post_idea_generator_autoload The singleton instance of the class.
	 */
	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Initializes the class instance. This method is private to enforce
	 * the singleton pattern and prevent direct instantiation.
	 * It requires the necessary files and initializes the helper class.
	 */
	public function __construct() {
		require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'vendor/autoload.php';
		require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'classes/class-ai-post-idea-generator-helpers.php';
		$this->helpers = ai_post_idea_generator_helpers::getInstance();
		require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'classes/class-ai-post-idea-generator-openai-service.php';
	}

	/**
	 * Initializes the plugin.
	 *
	 * This method is intended to contain the initialization logic
	 * , such as hooking into
	 * WordPress actions or filters.
	 *
	 * @return void
	 */
	public function init() {
		/**
		 * Enqueues the necessary assets for the AI Post Idea Generator plugin in the WordPress admin area.
		 *
		 * This function is hooked to the 'admin_enqueue_scripts' action and is responsible for
		 * loading the required CSS and JavaScript files when the admin dashboard is loaded.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'ai_post_idea_generator_enqueue_assets' ) );

		/**
		 * Adds the AI Post Idea Generator admin menu page to the WordPress admin menu.
		 *
		 * This function is hooked into the 'admin_menu' action and is responsible for
		 * creating the admin menu page for the AI Post Idea Generator plugin.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
		 */
		add_action( 'admin_menu', array( $this, 'ai_post_idea_admin_menu_page' ) );

		/**
		 * Adds an action hook to display admin notices.
		 *
		 * This function hooks the 'ai_post_idea_admin_notices' method to the 'admin_notices' action,
		 * which allows the method to display custom notices in the WordPress admin area.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/admin_notices/
		 */
		add_action( 'admin_notices', array( $this, 'ai_post_idea_admin_notices' ) );

		/**
		 * Registers the AJAX action for saving AI Post Idea Generator settings.
		 *
		 * This function hooks into the 'wp_ajax_ai_post_idea_generator_save_api_key' action,
		 * allowing the specified callback method 'ai_post_idea_generator_save_api_key' to be
		 * executed when the AJAX request is made.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
		 */
		add_action( 'wp_ajax_ai_post_idea_generator_save_api_key', array( $this, 'ai_post_idea_generator_save_api_key' ) );

		/**
		 * Registers the AJAX action for generating post ideas.
		 *
		 * This action is triggered when an AJAX request is made to 'wp_ajax_ai_post_idea_generator_generate_ideas'.
		 * It calls the 'ai_post_idea_generator_generate_ideas' method of this class to handle the request.
		 *
		 * @hook wp_ajax_ai_post_idea_generator_generate_ideas
		 */
		add_action( 'wp_ajax_ai_post_idea_generator_generate_ideas', array( $this, 'ai_post_idea_generator_generate_ideas' ) );

		/**
		 * Registers the AJAX action for creating drafts in the AI Post Idea Generator plugin.
		 *
		 * This function hooks into the 'wp_ajax_ai_post_idea_generator_create_drafts' action,
		 * allowing the creation of drafts via an AJAX request.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
		 */
		add_action( 'wp_ajax_ai_post_idea_generator_create_drafts', array( $this, 'ai_post_idea_generator_create_drafts' ) );
	}

	/**
	 * Enqueues the necessary styles and scripts.
	 *
	 * This function checks if the 'src/styles.css' file exists in the plugin directory.
	 * If it exists, it sets the version to the current timestamp to ensure the latest
	 * version of the file is loaded. Otherwise, it uses the defined plugin version.
	 *
	 * @return void
	 */
	public function ai_post_idea_generator_enqueue_assets() {
		$version = AI_POST_IDEA_GENERATOR_VERSION;
		if ( file_exists( AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'src/styles.css' ) ) {
			$version = time();
		}
		wp_enqueue_style( 'ai-post-idea-generator-styles', AI_POST_IDEA_GENERATOR_PLUGIN_URL . 'assets/styles.css', array(), $version );
		wp_enqueue_script( 'ai-post-idea-generator-scripts', AI_POST_IDEA_GENERATOR_PLUGIN_URL . 'assets/scripts.js', array( 'jquery' ), $version, true );
		wp_localize_script(
			'ai-post-idea-generator-scripts',
			'mawp',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mawp_nonce' ),
			)
		);
	}

	/**
	 * Adds the AI Post Idea Generator admin menu page to the WordPress dashboard.
	 *
	 * This function creates a new menu item in the WordPress admin sidebar under the
	 * 'manage_options' capability. The menu item is labeled 'AI Post Ideas' and uses
	 * the 'dashicons-lightbulb' icon. When clicked, it loads the admin page callback.
	 *
	 * @return void
	 */
	public function ai_post_idea_admin_menu_page() {
		add_menu_page(
			__( 'AI Post Idea Generator', 'ai-post-idea-generator' ),
			__( 'AI Post Ideas', 'ai-post-idea-generator' ),
			'manage_options',
			'ai-post-idea-generator',
			array( $this, 'ai_post_idea_generator_admin_page' ),
			'dashicons-lightbulb',
			20
		);
	}

	public function ai_post_idea_admin_notices() {
		$openai_api_key = $this->helpers->getSetting( 'openai_api_key', false );
		if ( ! $openai_api_key ) {
			echo $this->helpers->renderHtml( 'openai-api-key-notice' );
		}
	}

	/**
	 * Displays the content of the AI Post Idea Generator admin page.
	 *
	 * This function outputs a simple 'Hello World' message wrapped in a div with the
	 * class 'wrap' when the admin page is loaded.
	 *
	 * @return void
	 */
	public function ai_post_idea_generator_admin_page() {
		echo $this->helpers->renderHtml( 'admin-page' );
	}

	public function ai_post_idea_generator_save_api_key() {
		/**
		 * Checks the AJAX nonce for security and sends a JSON response if the nonce is invalid.
		 *
		 * This function verifies the AJAX request using the 'mawp_nonce' nonce. If the nonce
		 * verification fails, it sends a JSON response indicating failure with an appropriate
		 * error message.
		 *
		 * @return void
		 */
		if ( ! check_ajax_referer( 'mawp_nonce', '_ajax_nonce' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Something went wrong, please refresh this page and try again.', 'ai-post-idea-generator' ),
				)
			);
		}

		/**
		 * Checks if the current user has the 'manage_options' capability.
		 * If the user does not have the required capability, sends a JSON response
		 * indicating failure and a message stating that the user does not have permission
		 * to perform the action.
		 *
		 * @return void
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'You do not have permission to perform this action.', 'ai-post-idea-generator' ),
				)
			);
		}

		// format post data in key value pair
		$post_data = array();
		foreach ( $_POST['data'] as $item ) {
			$post_data[ $item['name'] ] = $item['value'];
		}

		// check if open api key is empty, if empty send error message
		if ( ! isset( $post_data['openai_api_key'] ) || $post_data['openai_api_key'] == '' ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'You must enter the OpenAI API key.', 'ai-post-idea-generator' ),
				)
			);
		}

		// update open api key in the database
		$this->helpers->updateSetting( 'openai_api_key', $post_data['openai_api_key'] );
		error_log( 'autoload: ' . $this->helpers->getSetting( 'openai_api_key' ) );

		/**
		 * Calls the OpenAI service to get assistants and handles the response.
		 *
		 * If the response indicates failure, deletes the 'openai_api_key' setting and sends a JSON response
		 * indicating the failure with an appropriate message.
		 */
		$openai_service = new ai_post_idea_generator_openai_service();
		$response       = $openai_service->getAssistants();
		if ( ! $response['success'] ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Invalid OpenAI API key, please check and try again.', 'ai-post-idea-generator' ),
				)
			);
		}

		/**
		 * Sends a JSON response indicating the OpenAI API key was saved successfully.
		 *
		 * @return void
		 */
		wp_send_json(
			array(
				'success' => true,
				'message' => __( 'OpenAI API key saved successfully.', 'ai-post-idea-generator' ),
			)
		);
	}

	public function ai_post_idea_generator_generate_ideas() {
		/**
		 * Checks the AJAX nonce for security and sends a JSON response if the nonce is invalid.
		 *
		 * This function verifies the AJAX request using the 'mawp_nonce' nonce. If the nonce
		 * verification fails, it sends a JSON response indicating failure with an appropriate
		 * error message.
		 *
		 * @return void
		 */
		if ( ! check_ajax_referer( 'mawp_nonce', '_ajax_nonce' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Something went wrong, please refresh this page and try again.', 'ai-post-idea-generator' ),
				)
			);
		}

		/**
		 * Checks if the current user has the 'manage_options' capability.
		 * If the user does not have the required capability, sends a JSON response
		 * indicating failure and a message stating that the user does not have permission
		 * to perform the action.
		 *
		 * @return void
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'You do not have permission to perform this action.', 'ai-post-idea-generator' ),
				)
			);
		}

		$post_data = array();
		foreach ( $_POST['data'] as $item ) {
			$post_data[ $item['name'] ] = $item['value'];
		}

		// get WordPress posts limit to 10 number
		$posts       = get_posts(
			array(
				'numberposts' => $post_data['knowledgebase_posts_count'],
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
		$post_titles = array();
		foreach ( $posts as $post ) {
			$post_titles[] = $post->post_title;
		}
		$post_titles = implode( ', ', $post_titles );

		$prompt         = "Create {$post_data['idea_posts_count']} blog posts titles based on these previous posts: {$post_titles} Return only titles in list format without numbers or bullets and nothing else.";
		$openai_service = new ai_post_idea_generator_openai_service();
		$openai_service->processPrompt( $prompt );

		wp_send_json(
			array(
				'success' => true,
				'message' => __( 'Post ideas generated successfully', 'ai-post-idea-generator' ),
			)
		);
	}

	public function ai_post_idea_generator_create_drafts() {
		/**
		 * Checks the AJAX nonce for security and sends a JSON response if the nonce is invalid.
		 *
		 * This function verifies the AJAX request using the 'mawp_nonce' nonce. If the nonce
		 * verification fails, it sends a JSON response indicating failure with an appropriate
		 * error message.
		 *
		 * @return void
		 */
		if ( ! check_ajax_referer( 'mawp_nonce', '_ajax_nonce' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Something went wrong, please refresh this page and try again.', 'ai-post-idea-generator' ),
				)
			);
		}

		/**
		 * Checks if the current user has the 'manage_options' capability.
		 * If the user does not have the required capability, sends a JSON response
		 * indicating failure and a message stating that the user does not have permission
		 * to perform the action.
		 *
		 * @return void
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'You do not have permission to perform this action.', 'ai-post-idea-generator' ),
				)
			);
		}

		// WordPress try catch statement
		try {
			foreach ( $_POST['data'] as $item ) {
				$post_id = wp_insert_post(
					array(
						'post_title'   => esc_html( trim( $item['value'] ) ),
						'post_status'  => 'draft',
						'post_type'    => 'post',
						'post_content' => '', // Add default content if needed
					)
				);
				update_post_meta( $post_id, '_ai_post_idea_generator', true );
			}
		} catch ( Exception $e ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Something went wrong, please try again.', 'ai-post-idea-generator' ),
				)
			);
		}

		$this->helpers->deleteSetting( 'openai_post_ideas' );

		wp_send_json(
			array(
				'success' => true,
				'message' => __( 'Drafts created successfully', 'ai-post-idea-generator' ),
			)
		);
	}
}
