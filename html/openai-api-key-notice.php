<?php if (isset($_GET['page']) && $_GET['page'] == 'ai-post-idea-generator') {?>
<div class="notice notice-error is-dismissible">
    <p>
        <?php echo sprintf(__('<b>The OpenAI API key is missing.</b><br>Please save your API key under the <a href="%s"><u>Settings tab</u></a> to use the AI Post Idea Generator.', 'ai-post-idea-generator'), admin_url('admin.php?page=ai-post-idea-generator&tab=settings')); ?>
    </p>
</div>
<?php }?>