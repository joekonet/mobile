<?php
/*
Template Name: blog
*/
require_once('../inc/default.php');
head('blogz');
?>
                  <!-- begin content column -->
                  <div id="myblog">                           
				<h2><span>Blog</span></h2>
                        
                        <!-- begin left column -->
                        <div id="blog-lcol">       
                                         
                        	<!-- begin blog post -->
                              <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                              <?php wp_link_pages(); ?><?php wp_link_pages(); ?><?php wp_link_pages(); ?><?php wp_link_pages(); ?><?php wp_link_pages(); ?><?php wp_link_pages(); ?>
                        	<div class="post" id="post-<?php the_ID(); ?>">
                                    <h3><?php the_title(); ?></h3>
						<?php the_content(__('(more...)')); ?>
                                    <div class="posted">Posted <?php the_time('F j, Y') ?> <?php edit_post_link(__('(Edit This)')); ?></div>
                                    <div class="blog-links">
                                          <?php comments_popup_link(__('<img src="/img/comments.gif" alt="Comments" />Comments (0) &middot;'), __('<img src="/img/comments.gif" alt="Comments" />Comments (1) &middot;'), __('<img src="/img/comments.gif" alt="Comments" />Comments (%) &middot;')); ?> 
                                          <a href="<?php the_permalink() ?>" title="Permalink">Permalink</a> &middot; Category: <?php the_category(',') ?>     
                                    </div>						
                        	</div>
					<?php comments_template(); // Get wp-comments.php template ?>
                              <!-- end blog post -->
					<?php endwhile; else: ?>
                                    <p class="sorry"><?php _e('Sorry, no posts matched your criteria.'); ?></p>
                              <?php endif; ?>  
                              <div class="navigation">
                                    <div class="alignleft"><?php next_posts_link('&laquo; Previous Entries') ?></div>
                                    <div class="alignright"><?php previous_posts_link('Next Entries &raquo;') ?></div>
                              </div>                                                          
                                                                                  
                        </div>
                        <!-- /end left column -->
                        
                        <span id="blog-rss"><a href="<?php bloginfo('rss2_url'); ?>" title="Syndicate this site using RSS" onclick="target='_blank'"><img src="/img/rss.gif" alt="" width="47px" height="73px" /></a></span>                        

                        <!-- begin right column -->
                        <div id="blog-rcol">
                        	<div class="blog-cat">                    
                                    <h4 id="hd-search"><span>Search</span></h4> 
                                    <form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
                                          <div>
                                          <input type="text" value="<?php echo attribute_escape($s); ?>" name="s" id="s" class="mailinput" /> 
                                          <input name="submit" type="image" value="Subscribe" src="/img/mag-glass.gif" id="mag-glass" />
                                          </div>
                                    </form>	                         										
                              </div> 
                        	<div class="blog-cat">                    
                              	<h4 id="hd-recent"><span>Recent</span></h4>
                                    <?php c2c_get_recent_posts(5); ?>                                                                     
                              </div>     
                        	<div class="blog-cat">                    
                              	<h4 id="hd-topics"><span>Topics</span></h4> 
                                    <?php wp_list_cats('sort_column=name&optioncount=1&hierarchical=0'); ?>                                                                                                                                                                                      
                              </div> 
                        	<div class="blog-cat">                    
                              	<h4 id="hd-archive"><span>Archive</span></h4> 
                                    <?php wp_get_archives('type=monthly'); ?>                                     
                              </div>                                                                                                                    
                        </div>
                        <!-- /end right column -->
                        
                        <div class="clear"></div>
                                                                                           
                  </div>
                  <!-- /end content column -->   
		
<?php
foot();
?>   