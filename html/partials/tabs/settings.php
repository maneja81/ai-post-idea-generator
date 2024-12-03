<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
$api_key = $this->getSetting('open_api_key', false);
if($api_key) {
    $api_key = $this->decryptString($api_key);
}
?>
<form method="post" class="form-wrap mawp-settings-form">
<div class="form-errors notice notice-error">
    <p><?php _e('Something went wrong, please try again.', 'lang-textdomain') ?></p>
</div>
<div class="form-success notice notice-success"><p></p></div>
<table class="form-table" role="presentation">
<tbody>
<?php
$description = wp_kses(sprintf('Enter your OpenAI API key to enable the AI Post Idea Generator.<br><a href="%s" target="_blank" rel="noopener noreferrer"><u>Click here</u></a> to obtain your OpenAI API key.', esc_url('https://platform.openai.com/api-keys')), ['br' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []], 'u' => []]);
echo $this->formField(['type' => 'password', 'name' => 'open_api_key', 'value' => $api_key, 'label' => 'OpenAI API Key', 'description' => $description, 'required' => true]);
?>
</tbody>
</table>
<p>
<input type="submit" name="save_settings" class="button button-primary" value="<?php _e('Save Settings', 'ai-post-idea-generator')?>">
</p>
</form>
