<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>
		<?php $args = array( 'post_type' => 'jknet_identity', 'posts_per_page' => 1 );?>
		<?php $loop = new WP_Query( $args );?>
		<?php while ( $loop->have_posts() ) : $loop->the_post();?>
		<!-- header -->
		<header>
			<div class="identity">
				<hgroup>
					<h1><?php the_field('name');?></h1>
					<h2><?php the_field('tagline');?></h2>
				</hgroup>
			</div>
			<div class="contact">
				<?php the_field('contact');?>
			</div>
		</header>
		<!-- /header -->
		
		<!-- objective -->
		<section class="objective">
			<aside class="col-left">
				<h3>Objective:</h3>
			</aside>
			<div class="col-right">
				<p><?php the_field('objective');?></p>					
			</div>
		</section>
		<!-- /objective -->
		<?php endwhile;?>
	
		<!-- experience -->
		<section class="experience">
			<aside class="col-left">
				<h3>Professional Experience:</h3>
			</aside>
			<div class="col-right">
				<?php $args = array( 'post_type' => 'jknet_experience', 'posts_per_page' => 10 );?>
				<?php $loop = new WP_Query( $args );?>
				<?php while ( $loop->have_posts() ) : $loop->the_post();?>				
				<article>
					<div class="event">
						<p>
							<strong><?php the_field('company_name');?></strong>
							<?php the_field('job_title');?>
						</p>			
						<p>
							<strong><?php the_field('job_duration');?></strong>
							<?php the_field('company_location');?>
						</p>
					</div>
					<div class="summary">
						<h4>Summary</h4>
						<?php if(get_field('job_summary')) : ?>
						<ul>
							<?php while(the_repeater_field('job_summary')) : ?>
								<li><?php the_sub_field('job_summary_list'); ?></li>
							<?php endwhile; ?>
						</ul>
						<?php endif; ?>
					</div>				
					<?php if(get_field('project_links')) : ?>
					<div class="featured">
						<h4>Featured Projects:</h4>
						<ul>
							<?php while(the_repeater_field('project_links')) : ?>
								<li><a href="<?php the_sub_field('project_link'); ?>" title="<?php the_sub_field('project_title'); ?>" target="_blank"><?php the_sub_field('project_title'); ?></a></li>
							<?php endwhile; ?>
						</ul>
						<?php endif; ?>
					</div>
				</article>
				<?php endwhile;?>
			</div>
		</section>
		<!-- /experience -->
		
		<!-- education -->
		<section class="education">
			<aside class="col-left">
				<h3>Education:</h3>
			</aside>
			<div class="col-right">
				<?php $args = array( 'post_type' => 'jknet_edu', 'posts_per_page' => 10 );?>
				<?php $loop = new WP_Query( $args );?>
				<?php while ( $loop->have_posts() ) : $loop->the_post();?>	
				<article>
					<div class="event">
						<p>
							<strong><?php the_field('school_name');?></strong>
							<?php the_field('specialty');?>
						</p>			
						<p>
							<strong><?php the_field('school_location');?></strong>
							<?php the_field('attendance_duration');?>
						</p>
					</div>
					<div class="featured">
						<?php if(get_field('attendance_summary')) : ?>
						<ul>
							<?php while(the_repeater_field('attendance_summary')) : ?>
								<li><?php the_sub_field('attendance_list'); ?></li>
							<?php endwhile; ?>
						</ul>
						<?php endif; ?>
					</div>
				</article>
				<?php endwhile;?>																		
			</div>
		</section>
		<!-- /education -->
		
		<!-- spotlight -->
		<section class="spotlight">
			<aside class="col-left">
				<h3>Technology Spotlight:</h3>
			</aside>
			<div class="col-right">
				<?php $args = array( 'post_type' => 'jknet_skills', 'posts_per_page' => 10 );?>
				<?php $loop = new WP_Query( $args );?>
				<?php while ( $loop->have_posts() ) : $loop->the_post();?>				
				<article>
					<div class="featured">
						<div class="spotlight-col">
							<h4>Languages:</h4>
							<?php if(get_field('languages')) : ?>
							<ul>
								<?php while(the_repeater_field('languages')) : ?>
									<li><?php the_sub_field('language_list'); ?></li>
								<?php endwhile; ?>							
							</ul>
							<?php endif; ?>
						</div>
						
						<div class="spotlight-col">
							<h4>Applications:</h4>	
							<?php if(get_field('applications')) : ?>
							<ul>
								<?php while(the_repeater_field('applications')) : ?>
									<li><?php the_sub_field('application_list'); ?></li>
								<?php endwhile; ?>								
							</ul>
							<?php endif; ?>
						</div>
					</div>
				</article>
				<?php endwhile;?>					
			</div>
		</section>
		<!-- /spotlight -->