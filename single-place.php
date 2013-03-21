<?php
/**
 * Template Name: Lieu
 *
 * Description: A page template for a single place
 */

get_header(); 
?>

<div id="primary">
	<div id="content" role="main">

	<?php while ( have_posts() ) : the_post(); // The loop ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
 
				<!-- Display featured image in right-aligned floating div -->
				<div style="float: right; margin: 10px">
					<?php the_post_thumbnail( array( 100, 100 ) ); ?>
				</div>

				<!-- Display Title -->
				<label>Lieu : </label><?php the_title(); ?>
				<br />

				<!-- Display Custom fields -->
				<label>Ville : </label>
				<?php echo esc_html( get_post_meta( get_the_ID(), 'city', true ) ); ?>
				<br />

				<!-- TODO ajouter autres champs (ex. map) -->

				<!-- Display Categories -->
				<label><?php _ex('Catégorie :', 'Label catégorie sur le template single', 'mba-places-manager-locale' ) ?></label>
				<?php  
				the_terms( $post->ID, 'places_categories' ,  ' ' );
				?>

			</header>
 
			<!-- Display content -->
			<div class="entry-content"><?php the_content(); ?></div>
			
		</article>

	<?php endwhile; // end of the loop. ?>

	</div>
</div>

<?php get_footer(); ?>