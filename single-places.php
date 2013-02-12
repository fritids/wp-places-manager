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

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
 
			<!-- Display featured image in right-aligned floating div -->
			<div style="float: right; margin: 10px">
				<?php the_post_thumbnail( array( 100, 100 ) ); ?>
			</div>

			<!-- Display Title and Author Name -->
			<strong>Lieu : </strong><?php the_title(); ?><br />
			<strong>Ville : </strong>
			<?php echo esc_html( get_post_meta( get_the_ID(), 'city', true ) ); ?>
			<br />

			<!-- TODO ajouter autres champs -->

			<label><?php _ex('Catégorie :', 'Label catégorie sur le template single', 'mba-places-manager-locale' ) ?></label>
			<?php  
			the_terms( $post->ID, 'places_categories' ,  ' ' );
			?>

			</header>
 
			<!-- Display content -->
			<div class="entry-content"><?php the_content(); ?></div>
			
		</article>

	</div>
</div>

<?php get_footer(); ?>