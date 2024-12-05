<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'default';
?>
<div class="wrap ai-post-idea-generator">
	<h1><?php _e( 'AI Post Idea Generator', 'ai-post-idea-generator' ); ?></h1>
	<p>
		<?php _e( 'This powerful tool helps you unlock creative content ideas by analyzing your existing posts.', 'ai-post-idea-generator' ); ?>
		<br>
		<?php _e( "Simply specify the number of posts you want the AI to analyze and the number of fresh post ideas you'd like to generate.", 'ai-post-idea-generator' ); ?>
		<br>
		<?php _e( 'With this streamlined process, you can effortlessly enhance your content strategy and keep your website engaging and relevant.', 'ai-post-idea-generator' ); ?>
	</p>
	<nav class="nav-tab-wrapper">
		<a href="<?php echo $this->tabUrl( 'default' ); ?>" class="<?php echo $this->tabClass( 'default' ); ?>">
			<?php _e( 'Generate Ideas', 'ai-post-idea-generator' ); ?>
		</a>
		<a href="<?php echo $this->tabUrl( 'settings' ); ?>" class="<?php echo $this->tabClass( 'settings' ); ?>">
			<?php _e( 'Settings', 'ai-post-idea-generator' ); ?>
		</a>
	</nav>
	<div class="tab-content">
	<?php echo $this->renderHtml( "partials/tabs/{$tab}" ); ?>
	</div>
</div>