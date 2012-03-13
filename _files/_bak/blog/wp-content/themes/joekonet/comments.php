<?php if ( !empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) : ?>
<p><?php _e('Enter your password to view comments.'); ?></p>
<?php return; endif; ?>

<?php if ( $comments ) : ?>
<ol id="commentlist">

<?php foreach ($comments as $comment) : ?>
	<li id="comment-<?php comment_ID() ?>">
	<?php comment_text() ?>
	<p><cite><?php comment_type(__('Comment'), __('Trackback'), __('Pingback')); ?> <?php _e('by'); ?> <?php comment_author_link() ?> &#8212; <?php comment_date() ?> @ <a href="#comment-<?php comment_ID() ?>"><?php comment_time() ?></a></cite> <?php edit_comment_link(__("Edit This"), ' |'); ?></p>
	</li>

<?php endforeach; ?>

</ol>

<?php else : // If there are no comments yet ?>
	<p><?php _e('No comments yet.'); ?></p>
<?php endif; ?>

<p><?php comments_rss_link(__('<abbr title="Really Simple Syndication">RSS</abbr> feed for comments on this post.')); ?> 
<?php if ( pings_open() ) : ?>
	<a href="<?php trackback_url() ?>" rel="trackback"><?php _e('TrackBack <abbr title="Uniform Resource Identifier">URI</abbr>'); ?></a>
<?php endif; ?>
</p>

<?php if ( comments_open() ) : ?>
<div id="postcomment"><?php _e('Leave a comment'); ?></div>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout &raquo;</a></p>

<?php else : ?>

<p>
<input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" class="mailinput" />
<small> Name<?php if ($req) _e(' *'); ?></small>
</p>

<p>
<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" class="mailinput" />
<small> Email (will not be published)<?php if ($req) _e(' *'); ?> </small>
</p>

<p>
<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" class="mailinput" />
<small> Website</small>
</p>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" rows="10" tabindex="4"></textarea></p>

<p><input name="submit" type="image" id="submit" tabindex="5" value="Submit Comment" src="/img/submission.gif" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php else : // Comments are closed ?>
<p><?php _e('Sorry, the comment form is closed at this time.'); ?></p>
<?php endif; ?>
