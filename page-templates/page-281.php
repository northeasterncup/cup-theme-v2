<?php

/**
 * The template for displaying the /home-html page.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Bootscore
 */

get_header();
?>
<style>
    .home-slider#container {
        height: 70vh;
    }
</style>

<div id="content" class="site-content">
    <div id="primary" class="content-area">

        <!-- Hook to add something nice -->
        <?php bs_after_primary(); ?>

        <main id="main" class="site-main">

            <div class="entry-content">
                <div class="container-fluid bg-dark home-slider" id="container">
                </div>
                <div class="container-fluid bg-primary home-cta" id="container">
                    <div class="row">
                        <div class="col"></div>
                        <div class="col bg-primary" id="cta-col">
                            <h2>We organize Northeastern's most memorable on-campus events.</h2>
                        </div>
                        <div class="col"></div>
                    </div>
                </div>
            </div>

        </main><!-- #main -->

    </div><!-- #primary -->
</div><!-- #content -->
<?php
get_footer();
