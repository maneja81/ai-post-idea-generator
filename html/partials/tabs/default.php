<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
$posts_count = wp_count_posts('post');
$posts_count = ($posts_count->publish >= 50) ? 50 : $posts_count->publish;
$ideas = $this->getSetting('openai_result', []);
if (isset($ideas['content'][0]['text']['value'])) {
    $ideas = $ideas['content'][0]['text']['value'];
}
?>
<form method="post" class="form-wrap mawp-generate-ideas">
    <div class="form-errors notice notice-error">
        <p><?php _e('Something went wrong, please try again.', 'lang-textdomain')?></p>
    </div>
    <div class="form-success notice notice-success"><p></p></div>
    <table class="form-table" role="presentation">
        <tbody>
            <?php echo $this->formField(['type' => 'number', 'name' => 'knowledgebase_posts_count', 'label' => 'Existing Posts to Analyze', 'description' => esc_html__("Specify the number of existing posts to include as the knowledge base for generating new post ideas. A larger number provides more context for the AI but may increase processing time."), 'required' => true, 'attributes' => 'min="1" max="50"'], $posts_count); ?>
            <?php echo $this->formField(['type' => 'number', 'name' => 'idea_posts_count', 'label' => 'Post Ideas to Generate', 'description' => esc_html__("Enter the number of post ideas you want the AI to generate."), 'required' => true, 'attributes' => 'min="10" max="50"'], 50); ?>
        </tbody>
    </table>
    <p>
        <button type="submit" class="button button-primary"><?php _e('Generate Post Ideas', 'ai-post-idea-generator');?></button>
    </p>
</form>
<div style="height:20px;"></div>
<table class="widefat fixed striped">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-title">
                <?php _e('Post Title', 'ai-post-idea-generator'); ?>
            </th>
            <th width="15%" scope="col" class="manage-column column-actions">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <?php for($i=0;$i<=30;$i++) { ?>
        <tr>
            <td>Hello</td>
            <td style="text-align:right;">
                <a href="">Create Draft</a>
                <span>|</span>
                <a href="">Delete</a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
