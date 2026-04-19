<?php
/**
 * Template Name: Kitchen Sink
 *
 * A specialized template for displaying all UI components and design tokens.
 *
 * @package Silveira
 */

get_header(); ?>

<style>
    /* Styling just for the styleguide page */
    .sg-section {
        margin-bottom: 80px;
        padding-bottom: 40px;
        border-bottom: 1px dashed #ccc;
    }
    .sg-section__title {
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 12px;
        margin-bottom: 30px;
        color: #666;
    }
    .sg-color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 20px;
    }
    .sg-swatch {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .sg-swatch__box {
        aspect-ratio: 16/9;
        border-radius: 4px;
        border: 1px solid #eee;
    }
    .sg-swatch__label {
        font-size: 12px;
        font-family: var(--sil-font-mono);
    }
    .sg-grid-visual {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--sil-font-mono);
        font-size: 12px;
        margin-bottom: 20px;
    }
    .sg-btn-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }
</style>

<main id="primary" class="site-main o-container" style="padding-top: 50px; padding-bottom: 100px;">

    <!-- TOPOGRAPHY -->
    <section class="sg-section">
        <h2 class="sg-section__title">Typography</h2>
        <div class="sg-typography">
            <h1>Heading Level 1 (Isaac)</h1>
            <h2>Heading Level 2 (Isaac)</h2>
            <h3>Heading Level 3 (Isaac)</h3>
            <h4>Heading Level 4 (Isaac)</h4>
            <p>Body text (Isaac). Standard paragraph text for content readability. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.</p>
            <p style="font-family: var(--sil-font-mono);">Monospaced Text (Akkurat-Mono). Used for identifiers or code snippets.</p>
        </div>
    </section>

    <!-- COLORS -->
    <section class="sg-section">
        <h2 class="sg-section__title">Colors</h2>
        
        <h3 style="margin-bottom: 20px;">Violeta</h3>
        <div class="sg-color-grid">
            <?php foreach(['050', '100', '200', '300', '400', '500', '600', '700', '800', '900'] as $shade): ?>
                <div class="sg-swatch">
                    <div class="sg-swatch__box" style="background-color: var(--sil-violeta-<?php echo $shade; ?>);"></div>
                    <span class="sg-swatch__label">violeta-<?php echo $shade; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <h3 style="margin-top: 40px; margin-bottom: 20px;">Verde</h3>
        <div class="sg-color-grid">
            <?php foreach(['050', '100', '200', '300', '400', '500', '600', '700', '800', '900'] as $shade): ?>
                <div class="sg-swatch">
                    <div class="sg-swatch__box" style="background-color: var(--sil-verde-<?php echo $shade; ?>);"></div>
                    <span class="sg-swatch__label">verde-<?php echo $shade; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- MAP FILTERS / FORMS -->
    <section class="sg-section">
        <h2 class="sg-section__title">Forms & Map Filters</h2>
        
        <div class="c-filter-bar">
            <!-- Select 1: Modalidade -->
            <div class="c-filter-bar__item">
                <div class="c-select">
                    <span class="c-select__label">Modalidade educativa</span>
                    <span class="c-select__value">Que procuras?</span>
                    
                    <!-- Simulating open dropdown state for the first item -->
                    <div class="c-select__dropdown c-select__dropdown--open">
                        <label class="c-checkbox">
                            <input type="checkbox" class="c-checkbox__input">
                            <span class="c-checkbox__box"></span>
                            <span class="c-checkbox__label">Formal</span>
                        </label>
                        <label class="c-checkbox">
                            <input type="checkbox" class="c-checkbox__input" checked>
                            <span class="c-checkbox__box"></span>
                            <span class="c-checkbox__label">Nom formal</span>
                        </label>
                        <label class="c-checkbox">
                            <input type="checkbox" class="c-checkbox__input">
                            <span class="c-checkbox__box"></span>
                            <span class="c-checkbox__label">Informal</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Select 2: Comarca -->
            <div class="c-filter-bar__item">
                <div class="c-select">
                    <span class="c-select__label">Comarca</span>
                    <span class="c-select__value">Em que zona?</span>
                </div>
            </div>

            <!-- Select 3: Localidade -->
            <div class="c-filter-bar__item">
                <div class="c-select">
                    <span class="c-select__label">Localidade</span>
                    <span class="c-select__value">Em que vila?</span>
                </div>
            </div>
        </div>
    </section>

    <!-- BUTTONS -->
    <section class="sg-section">
        <h2 class="sg-section__title">Buttons</h2>
        
        <h3 style="margin-bottom: 15px;">Primary</h3>
        <div class="sg-btn-wrap" style="margin-bottom: 40px;">
            <button class="c-btn c-btn--primary c-btn--xl">Primary XL</button>
            <button class="c-btn c-btn--primary c-btn--l">Primary L</button>
            <button class="c-btn c-btn--primary c-btn--xl c-btn--has-icon">
                <span class="c-btn__icon">★</span> Primary Icon XL
            </button>
            <button class="c-btn c-btn--primary c-btn--xl" disabled>Disabled</button>
        </div>

        <h3 style="margin-bottom: 15px;">Secondary</h3>
        <div class="sg-btn-wrap">
            <button class="c-btn c-btn--secondary c-btn--xl">Secondary XL</button>
            <button class="c-btn c-btn--secondary c-btn--l">Secondary L</button>
            <button class="c-btn c-btn--secondary c-btn--xl c-btn--has-icon">
                <span class="c-btn__icon">★</span> Secondary Icon
            </button>
            <button class="c-btn c-btn--secondary c-btn--xl" disabled>Disabled</button>
        </div>
    </section>

    <!-- GRID (FLEX) -->
    <section class="sg-section">
        <h2 class="sg-section__title">Responsive Flex Grid (12 Columns)</h2>
        
        <h3 style="margin-bottom: 10px; font-size: 14px;">12 Columns (Default)</h3>
        <div class="o-row">
            <?php for($i=1; $i<=12; $i++): ?>
                <div class="o-col-1">
                    <div class="sg-grid-visual">1/12</div>
                </div>
            <?php endfor; ?>
        </div>

        <h3 style="margin-top: 30px; margin-bottom: 10px; font-size: 14px;">Spans (Stacked on mobile, side by side on desktop)</h3>
        <div class="o-row" style="margin-top: 20px;">
            <div class="o-col-12 o-col-md-4">
                <div class="sg-grid-visual">col-12 col-md-4</div>
            </div>
            <div class="o-col-12 o-col-md-4">
                <div class="sg-grid-visual">col-12 col-md-4</div>
            </div>
            <div class="o-col-12 o-col-md-4">
                <div class="sg-grid-visual">col-12 col-md-4</div>
            </div>
        </div>

        <h3 style="margin-top: 30px; margin-bottom: 10px; font-size: 14px;">Halves and Offsets</h3>
        <div class="o-row" style="margin-top: 20px;">
            <div class="o-col-12 o-col-lg-6">
                <div class="sg-grid-visual">col-12 col-lg-6</div>
            </div>
            <div class="o-col-12 o-col-lg-6">
                <div class="sg-grid-visual">col-12 col-lg-6</div>
            </div>
            <div class="o-col-10 o-offset-1">
                <div class="sg-grid-visual">col-10 offset-1</div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
