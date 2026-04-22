<?php
/**
 * Template part for displaying single 프로젝트 (Projects)
 *
 * @package Silveira
 */

$post_id = get_the_ID();
$lema = get_field( 'projeto_lema' );
$endereco = get_field( 'projeto_endereco' );
$modalidades = get_the_terms( $post_id, 'modalidade_projeto' );
$territorios = get_the_terms( $post_id, 'territorio' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'c-project-single' ); ?>>
    
    <div class="o-container">
        <div class="o-row">
            <div class="o-col-8">
                
                <header class="c-project-single__header">
                    <?php if ( ! empty( $modalidades ) ) : ?>
                        <div class="c-project-single__meta">
                            <?php foreach ( $modalidades as $mod ) : ?>
                                <span class="c-tag"><?php echo esc_html( $mod->name ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="c-project-single__title"><?php the_title(); ?></h1>
                    
                    <?php if ( $lema ) : ?>
                        <p class="c-project-single__lema"><?php echo esc_html( $lema ); ?></p>
                    <?php endif; ?>
                </header>

                <div class="c-project-single__content">
                    <?php the_content(); ?>
                </div>

            </div>

            <div class="o-col-4">
                <aside class="c-project-single__sidebar">
                    
                    <?php if ( $endereco ) : ?>
                        <div class="c-project-info-block">
                            <h4 class="c-project-info-block__title"><?php esc_html_e( 'Endereço', 'silveira' ); ?></h4>
                            <p class="c-project-info-block__text">
                                <span class="o-icon o-icon--xs">location_on</span>
                                <?php echo esc_html( $endereco ); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $territorios ) ) : ?>
                        <div class="c-project-info-block">
                            <h4 class="c-project-info-block__title"><?php esc_html_e( 'Território', 'silveira' ); ?></h4>
                            <div class="c-project-info-block__tags">
                                <?php echo get_the_term_list( $post_id, 'territorio', '', ', ', '' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </aside>
            </div>
        </div>
    </div>

</article>
