<?php

/**
 * Class ai_post_idea_generator_helpers
 *
 * A singleton class that provides helper functions.
 */
if (!class_exists('ai_post_idea_generator_helpers')) {
    class ai_post_idea_generator_helpers
    {

        /**
         * Singleton instance of the class.
         *
         * @var self
         */
        private static $instance;

        /**
         * Retrieves the singleton instance of the class.
         *
         * @return self The singleton instance of the class.
         */
        public static function getInstance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Generates the URL for a specific tab in the AI Post Idea Generator plugin.
         *
         * @param string $tab The tab identifier for which the URL is to be generated.
         * @return string The complete URL for the specified tab.
         */
        public function tabUrl(string $tab)
        {
            return admin_url('admin.php') . "?page=ai-post-idea-generator&tab={$tab}";
        }

        /**
         * Returns the class name for a given tab.
         *
         * @param string $tab The name of the tab.
         * @return string The class name for the tab.
         */
        public function tabClass(string $tab)
        {
            $class[] = 'nav-tab';
            if (!isset($_GET['tab']) && $tab === 'default') {
                $class[] = 'nav-tab-active';
            } else if (isset($_GET['tab']) && $_GET['tab'] === $tab) {
                $class[] = 'nav-tab-active';
            }
            return implode(' ', $class);
        }

        /**
         * Renders the HTML content from a specified file path.
         *
         * This method checks if a PHP file exists in the 'html' directory of the plugin,
         * and if it does, it captures and returns the output of that file.
         *
         * @param string $path The relative path (without '.php' extension) to the HTML file within the 'html' directory.
         * @return string The rendered HTML content, or an empty string if the file does not exist.
         */
        public function renderHtml(string $path)
        {
            if (file_exists(AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'html/' . $path . '.php')) {
                ob_start();
                require_once AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'html/' . $path . '.php';
                return ob_get_clean();
            }
            return '';
        }

        /**
         * Generates a form field based on the provided field configuration.
         *
         * @param array  $field          The configuration array for the form field.
         *                               - 'type' (string) Optional. The type of the form field. Default 'text'.
         *                               - 'name' (string) Optional. The name attribute of the form field. Default ''.
         *                               - 'label' (string) Optional. The label for the form field. Default ''.
         *                               - 'description' (string) Optional. The description for the form field. Default ''.
         *                               - 'options' (array) Optional. The options for select fields. Default [].
         *                               - 'value' (mixed) Optional. The value of the form field. Default is the provided $default_value.
         *                               - 'class' (string) Optional. The CSS class for the form field. Default ''.
         *                               - 'required' (bool) Optional. Whether the field is required. Default false.
         *                               - 'attributes' (string) Optional. Additional attributes for the form field. Default ''.
         * @param mixed  $default_value  The default value for the form field if not specified in the $field array.
         *
         * @return string The HTML output of the form field.
         */
        public function formField($field, $default_value = '')
        {
            $type = isset($field['type']) ? $field['type'] : 'text';
            $name = isset($field['name']) ? $field['name'] : '';
            $id = $name;
            $label = isset($field['label']) ? $field['label'] : '';
            $description = isset($field['description']) ? $field['description'] : '';
            $options = isset($field['options']) ? $field['options'] : [];
            $value = isset($field['value']) ? $field['value'] : $default_value;
            $class = isset($field['class']) ? $field['class'] : '';
            $required = isset($field['required']) ? 'required' : '';
            $attributes = isset($field['attributes']) ? $field['attributes'] : '';
            ob_start();
            echo '<tr>';
            echo '<th scope="row">';
            echo '<label for="' . esc_attr($id) . '">';
            echo esc_html($label);
            echo ($required != '') ? ' <span class="required">*</span>' : '';
            echo '</label>';
            echo '</th>';
            echo '<td>';
            include AI_POST_IDEA_GENERATOR_PLUGIN_DIR . 'html/partials/form-fields/' . $type . '.php';
            if ($description !== '') {
                echo '<p class="description">';
                echo $description;
                echo '</p>';
            }
            echo '</td>';
            echo '</tr>';
            return ob_get_clean();
        }

        /**
         * Retrieves the AI Post Idea Generator settings.
         *
         * If a key is provided, it returns the value associated with that key.
         * If no key is provided, it returns all settings.
         *
         * @param string|null $key The specific setting key to retrieve. Default is null.
         * @return mixed The value of the specified setting key, or all settings if no key is provided.
         */
        public function settings($key = null)
        {
            $settings = get_option('_ai_post_idea_generator_settings', []);
            if ($key !== null) {
                return isset($settings[$key]) ? $settings[$key] : null;
            }
            return $settings;
        }

        /**
         * Retrieves a specific setting value from the AI Post Idea Generator settings.
         *
         * @param string $key The key of the setting to retrieve.
         * @param mixed $default_value The default value to return if the setting is not found. Default is an empty string.
         * @return mixed The value of the setting if found, otherwise the default value.
         */
        public function getSetting(string $key, $default_value = '')
        {
            $settings = get_option('_ai_post_idea_generator_settings', []);
            return isset($settings[$key]) ? $settings[$key] : $default_value;
        }

        /**
         * Updates or add a specific setting in the AI Post Idea Generator plugin.
         *
         * This method retrieves the current settings from the WordPress options table,
         * adds or updates the specified setting with the provided value, and then saves the
         * updated settings back to the options table.
         *
         * @param string $key   The key of the setting to update.
         * @param mixed  $value The new value for the specified setting.
         * @return array The updated settings array.
         */
        public function updateSetting($key, $value)
        {
            $settings = get_option('_ai_post_idea_generator_settings', []);
            $settings[$key] = $value;
            update_option('_ai_post_idea_generator_settings', $settings);
            return $settings;
        }

        /**
         * Deletes a specific setting from the AI Post Idea Generator settings.
         *
         * This function retrieves the current settings from the '_ai_post_idea_generator_settings' option,
         * checks if the specified key exists, and if so, removes it from the settings array.
         * The updated settings array is then saved back to the '_ai_post_idea_generator_settings' option.
         *
         * @param string $key The key of the setting to be deleted.
         * @return array The updated settings array after the specified key has been removed.
         */
        public function deleteSetting($key)
        {
            $settings = get_option('_ai_post_idea_generator_settings', []);
            if (isset($settings[$key])) {
                unset($settings[$key]);
                update_option('_ai_post_idea_generator_settings', $settings);
            }
            return $settings;
        }

        /**
         * Updates the AI Post Idea Generator settings with the provided data.
         *
         * @param array $data The data to update the settings with.
         * @return bool True if the settings were updated successfully, false otherwise.
         */
        public function updateSettings($data)
        {
            return update_option('_ai_post_idea_generator_settings', $data);
        }

        /**
         * Removes the AI Post Idea Generator settings from the WordPress options table.
         *
         * This function deletes the '_ai_post_idea_generator_settings' option from the
         * WordPress database, effectively removing all settings related to the AI Post
         * Idea Generator plugin.
         *
         * @return bool True if the option was successfully deleted, false otherwise.
         */
        public function removeSettings()
        {
            return delete_option('_ai_post_idea_generator_settings');
        }

        public function encryptString($plaintext)
        {
            $cipher = 'AES-256-CBC';
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $key = sha1(md5(AI_POST_IDEA_GENERATOR_PLUGIN_DIR));

            $encrypted = openssl_encrypt($plaintext, $cipher, $key, 0, $iv);
            if ($encrypted === false) {
                return false;
            }

            return base64_encode($iv . $encrypted);
        }

        public function decryptString($encrypted)
        {
            $cipher = 'AES-256-CBC';
            $data = base64_decode($encrypted);
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = substr($data, 0, $ivlen);
            $ciphertext = substr($data, $ivlen);
            $key = sha1(md5(AI_POST_IDEA_GENERATOR_PLUGIN_DIR));
            return openssl_decrypt($ciphertext, $cipher, $key, 0, $iv);
        }

    }
}
