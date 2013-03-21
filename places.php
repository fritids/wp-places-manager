<?php
/**
 * Template Name: Listing lieux
 *
 * Description: A page template for places
 */

get_header();

// TODOs
// Filtrage par catégories ?
// Revoir affichage listing
// Moteur de recherche ?

?>

<div id="primary">
    <div id="content" role="main">
    <?php
    $mypost = array( 'post_type' => 'place', );
    $loop = new WP_Query( $mypost );
    ?>
    <?php while ( $loop->have_posts() ) : $loop->the_post();?>
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

                <strong>Catégorie : </strong>
                <?php
                the_terms( $post->ID, 'places_categories' ,  ' ' );
                ?>

            </header>

            <!-- Display movie review contents -->
            <div class="entry-content"><?php the_content(); ?></div>

        </article>

    <?php endwhile; ?>
    </div>
</div>

<?php wp_reset_query(); ?>
<?php get_footer(); ?>