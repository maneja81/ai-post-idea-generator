<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// handle cancel drafts action
if (isset($_GET['cancel-drafts'])) {
    $this->deleteSetting('openai_post_ideas');
    wp_redirect(admin_url('admin.php?page=ai-post-idea-generator'));
}

$posts_count = wp_count_posts('post');
$posts_count = ($posts_count->publish >= 50) ? 50 : $posts_count->publish;
$ideas = $this->getSetting('openai_post_ideas', []);
if (isset($ideas['content'][0]['text']['value'])) {
    $ideas = $ideas['content'][0]['text']['value'];
    $ideas = explode("\n", $ideas);
}

?>
<?php if (count($ideas) == 0) {?>
<form method="post" class="form-wrap mawp-generate-ideas">
    <div class="form-errors notice notice-error">
        <p><?php _e('Something went wrong, please try again.', 'ai-post-idea-generator')?></p>
    </div>
    <div class="form-success notice notice-success"><p></p></div>
    <table class="form-table" role="presentation">
        <tbody>
            <?php echo $this->formField(['type' => 'number', 'name' => 'knowledgebase_posts_count', 'label' => 'Existing Posts to Analyze', 'description' => esc_html__("Specify the number of existing posts to include as the knowledge base for generating new post ideas. A larger number provides more context for the AI but may increase processing time."), 'required' => true, 'attributes' => 'min="1" max="50"'], $posts_count); ?>
            <?php echo $this->formField(['type' => 'number', 'name' => 'idea_posts_count', 'label' => 'Post Ideas to Generate', 'description' => esc_html__("Enter the number of post ideas you want the AI to generate."), 'required' => true, 'attributes' => 'min="10" max="50"'], 20); ?>
        </tbody>
    </table>
    <p>
        <input type="submit" class="button button-primary" value="<?php _e('Generate Post Ideas', 'ai-post-idea-generator');?>" />
    </p>
</form>
<?php }?>
<?php if (count($ideas) > 0) {?>
<!-- <div style="height:20px;"></div> -->
<p>
<?php _e('<b>Below is a list of generated post ideas.</b><br>This will create draft posts with only the titles.', 'ai-post-idea-generator'); ?>
</p>
<form method="post" class="mawp-create-drafts">
<div class="form-errors notice notice-error">
    <p><?php _e('Something went wrong, please try again.', 'ai-post-idea-generator')?></p>
</div>
<div class="form-success notice notice-success"><p></p></div>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column">
                <input type="checkbox" id="select-all">
            </td>
            <th scope="col" class="manage-column column-title">
                <?php _e('Blog Post Ideas', 'ai-post-idea-generator');?>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($ideas as $idea) {?>
        <tr>
            <th scope="row" class="check-column">
                <input type="checkbox" class="item-checkbox" name="create_posts[]" value="<?php echo $idea; ?>" />
            </th>
            <td><?php echo $idea; ?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<p>
    <input type="submit" class="button button-primary" value="<?php _e('Create Drafts', 'ai-post-idea-generator');?>" />
    <span>&nbsp;</span>
    <a href="<?php echo admin_url('admin.php?page=ai-post-idea-generator&cancel-drafts=1'); ?>" class="button button-secondary"><?php _e('Cancel', 'ai-post-idea-generator');?></a>
</p>
</form>
<?php }?>