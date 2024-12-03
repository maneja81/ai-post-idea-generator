<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
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
class ai_post_idea_generator_autoload
{

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

    public $openai_service;

    /**
     * Gets the singleton instance of the class.
     *
     * If an instance of the class does not already exist, it creates one.
     *
     * @return ai_post_idea_generator_autoload The singleton instance of the class.
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
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
    public function __construct()
    {
        require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'vendor/autoload.php';
        require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'classes/class-ai-post-idea-generator-helpers.php';
        $this->helpers = ai_post_idea_generator_helpers::getInstance();
        require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'classes/class-ai-post-idea-generator-openai-service.php';
        $this->openai_service = ai_post_idea_generator_openai_service::getInstance();
    }

    /**
     * Initializes the plugin.
     *
     * This method is intended to contain the initialization logic
     *, such as hooking into
     * WordPress actions or filters.
     *
     * @return void
     */
    public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'ai_post_idea_generator_enqueue_assets'));
        add_action('admin_menu', array($this, 'ai_post_idea_admin_menu_page'));

        /**
         * Registers the AJAX action for saving AI Post Idea Generator settings.
         *
         * This function hooks into the 'wp_ajax_ai_post_idea_generator_save_api_key' action,
         * allowing the specified callback method 'ai_post_idea_generator_save_api_key' to be
         * executed when the AJAX request is made.
         *
         * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
         */
        add_action('wp_ajax_ai_post_idea_generator_save_api_key', array($this, 'ai_post_idea_generator_save_api_key'));

        /**
         * Registers the AJAX action for generating post ideas.
         *
         * This action is triggered when an AJAX request is made to 'wp_ajax_ai_post_idea_generator_generate_ideas'.
         * It calls the 'ai_post_idea_generator_generate_ideas' method of this class to handle the request.
         *
         * @hook wp_ajax_ai_post_idea_generator_generate_ideas
         */
        add_action('wp_ajax_ai_post_idea_generator_generate_ideas', array($this, 'ai_post_idea_generator_generate_ideas'));
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
    public function ai_post_idea_generator_enqueue_assets()
    {
        $version = AI_POST_IDEA_GENERATOR_VERSION;
        if (file_exists(AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'src/styles.css')) {
            $version = time();
        }
        wp_enqueue_style('ai-post-idea-generator-styles', AI_POST_IDEA_GENERATOR_PLUGIN_URL . 'assets/styles.css', [], $version);
        wp_enqueue_script('ai-post-idea-generator-scripts', AI_POST_IDEA_GENERATOR_PLUGIN_URL . 'assets/scripts.js', ['jquery'], $version, true);
        wp_localize_script('ai-post-idea-generator-scripts', 'mawp', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mawp_nonce'),
        ]);
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
    public function ai_post_idea_admin_menu_page()
    {
        add_menu_page(
            __('AI Post Idea Generator', 'ai-post-idea-generator'),
            __('AI Post Ideas', 'ai-post-idea-generator'),
            'manage_options',
            'ai-post-idea-generator',
            array($this, 'ai_post_idea_generator_admin_page'),
            'dashicons-lightbulb',
            20
        );
    }

    /**
     * Displays the content of the AI Post Idea Generator admin page.
     *
     * This function outputs a simple 'Hello World' message wrapped in a div with the
     * class 'wrap' when the admin page is loaded.
     *
     * @return void
     */
    public function ai_post_idea_generator_admin_page()
    {
        echo $this->helpers->renderHtml('admin-page');
    }

    public function ai_post_idea_generator_save_api_key()
    {
        /**
         * Checks the AJAX nonce for security and sends a JSON response if the nonce is invalid.
         *
         * This function verifies the AJAX request using the 'mawp_nonce' nonce. If the nonce
         * verification fails, it sends a JSON response indicating failure with an appropriate
         * error message.
         *
         * @return void
         */
        if (!check_ajax_referer('mawp_nonce', '_ajax_nonce')) {
            wp_send_json([
                'success' => false,
                'message' => __('Something went wrong, please refresh this page and try again.', 'ai-post-idea-generator'),
            ]);
        }

        /**
         * Checks if the current user has the 'manage_options' capability.
         * If the user does not have the required capability, sends a JSON response
         * indicating failure and a message stating that the user does not have permission
         * to perform the action.
         *
         * @return void
         */
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'success' => false,
                'message' => __('You do not have permission to perform this action.', 'ai-post-idea-generator'),
            ]);
        }

        /**
         * Processes the POST request data to handle the OpenAI API key.
         *
         * Iterates through the POST data and checks for the 'open_api_key' field.
         * If the 'open_api_key' field is empty, it sends a JSON response indicating failure.
         * If the 'open_api_key' field is not empty, it encrypts the value and updates the setting.
         *
         * @param array $_POST['data'] The POST data containing the fields and values.
         * @return void
         */
        foreach ($_POST['data'] as $item) {
            if ($item['name'] === 'open_api_key' && empty($item['value'])) {
                wp_send_json([
                    'success' => false,
                    'message' => __('You must enter the OpenAI API key.', 'ai-post-idea-generator'),
                ]);
            }
            if ($item['name'] === 'open_api_key' && !empty($item['value'])) {
                $value = $this->helpers->encryptString($item['value']);
                $this->helpers->updateSetting($item['name'], $value);
            }
        }

        /**
         * Calls the OpenAI service to get assistants and handles the response.
         *
         * If the response indicates failure, deletes the 'open_api_key' setting and sends a JSON response
         * indicating the failure with an appropriate message.
         *
         * @return void
         */
        $response = $this->openai_service->getAssistants();
        if (!$response['success']) {
            $this->helpers->deleteSetting('open_api_key');
            wp_send_json([
                'success' => false,
                'message' => __('Invalid OpenAI API key, please check and try again.', 'ai-post-idea-generator'),
            ]);
        }

        /**
         * Sends a JSON response indicating the OpenAI API key was saved successfully.
         *
         * @return void
         */
        wp_send_json([
            'success' => true,
            'message' => __('OpenAI API key saved successfully.', 'ai-post-idea-generator'),
        ]);
    }

    public function ai_post_idea_generator_generate_ideas()
    {
        /**
         * Checks the AJAX nonce for security and sends a JSON response if the nonce is invalid.
         *
         * This function verifies the AJAX request using the 'mawp_nonce' nonce. If the nonce
         * verification fails, it sends a JSON response indicating failure with an appropriate
         * error message.
         *
         * @return void
         */
        if (!check_ajax_referer('mawp_nonce', '_ajax_nonce')) {
            wp_send_json([
                'success' => false,
                'message' => __('Something went wrong, please refresh this page and try again.', 'ai-post-idea-generator'),
            ]);
        }

        /**
         * Checks if the current user has the 'manage_options' capability.
         * If the user does not have the required capability, sends a JSON response
         * indicating failure and a message stating that the user does not have permission
         * to perform the action.
         *
         * @return void
         */
        if (!current_user_can('manage_options')) {
            wp_send_json([
                'success' => false,
                'message' => __('You do not have permission to perform this action.', 'ai-post-idea-generator'),
            ]);
        }

        $post_data = [];
        foreach ($_POST['data'] as $item) {
            $post_data[$item['name']] = $item['value'];
        }

        // get wordpress posts limit to 10 number
        $posts = get_posts([
            'numberposts' => $post_data['knowledgebase_posts_count'],
            'post_type' => 'post',
            'post_status' => 'publish',
        ]);
        $post_titles = [];
        foreach ($posts as $post) {
            $post_titles[] = $post->post_title;
        }
        $post_titles = implode(', ', $post_titles);

        $prompt = "Create {$post_data['idea_posts_count']} blog posts titles based on these previous posts: {$post_titles} Return only titles in list format and nothing else.";

        wp_send_json([
            'success' => true,
            'message' => $prompt,
        ]);

    }

}
