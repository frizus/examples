<?php
defined( 'ABSPATH' ) || exit;

/**
 * Template loader class.
 */
class WC_Theme_Customisations_Template_Loader {
    private static $shop_page_id = 0;

    private static $theme_support = false;

    private static $theme_customisations_dir = 'theme-customisations/';

    private static $theme_customisations_template_directory;

    public static function init() {
        self::$theme_support = wc_current_theme_supports_woocommerce_or_fse();
        self::$shop_page_id  = wc_get_page_id( 'shop' );
        self::$theme_customisations_template_directory = plugin_dir_path(__FILE__).'../custom/templates/woocommerce/';

        if ( self::$theme_support ) {
            remove_filter('template_include', [WC_Template_Loader::class, 'template_loader']);
        } else {
            remove_action('template_redirect', [__CLASS__, 'unsupported_theme_init']);
        }
        add_filter('template_include', [__CLASS__, 'template_loader']);
    }

    public static function template_loader( $template ) {
        if ( is_embed() ) {
            return $template;
        }

        $default_file = self::get_template_loader_default_file();

        if ( $default_file ) {
            /**
             * Filter hook to choose which files to find before WooCommerce does it's own logic.
             *
             * @since 3.0.0
             * @var array
             */
            $search_files = self::get_template_loader_files( $default_file );
            $template     = self::locate_template( $search_files );

            if ( ! $template ) {
                return WC_Template_Loader::template_loader($template);
            } else {
                return $template;
            }
        }

        return WC_Template_Loader::template_loader($template);
    }

    private static function locate_template( $template_names, $load = false, $require_once = true, $args = array() ) {
        $located = '';
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name ) {
                continue;
            }
            if ( file_exists( self::$theme_customisations_template_directory . $template_name ) ) {
                $located = self::$theme_customisations_template_directory . $template_name;
                break;
            }
        }

        if ( $load && '' !== $located ) {
            load_template( $located, $require_once, $args );
        }

        return $located;
    }

    private static function get_template_loader_default_file() {
        if (
            is_singular( 'product' ) &&
            ! self::has_block_template( 'single-product' )
        ) {
            $default_file = 'single-product.php';
        } elseif ( is_product_taxonomy() ) {
            $object = get_queried_object();

            if ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) {
                if ( self::has_block_template( 'taxonomy-' . $object->taxonomy ) ) {
                    $default_file = '';
                } else {
                    $default_file = 'taxonomy-' . $object->taxonomy . '.php';
                }
            } elseif ( ! self::has_block_template( 'archive-product' ) ) {
                $default_file = 'archive-product.php';
            }
        } elseif (
            ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) &&
            ! self::has_block_template( 'archive-product' )
        ) {
            $default_file = 'archive-product.php';
        } else {
            $default_file = '';
        }
        return $default_file;
    }

    private static function has_block_template( $template_name ) {
        if ( ! $template_name ) {
            return false;
        }

        $has_template            = false;
        $template_filename       = $template_name . '.html';
        // Since Gutenberg 12.1.0, the conventions for block templates directories have changed,
        // we should check both these possible directories for backwards-compatibility.
        $possible_templates_dirs = array( 'templates', 'block-templates' );

        // Combine the possible root directory names with either the template directory
        // or the stylesheet directory for child themes, getting all possible block templates
        // locations combinations.
        $filepath        = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_filename;
        $possible_paths  = array(
            self::$theme_customisations_template_directory . $filepath,
        );

        // Check the first matching one.
        foreach ( $possible_paths as $path ) {
            if ( is_readable( $path ) ) {
                $has_template = true;
                break;
            }
        }

        return $has_template;
    }

    private static function get_template_loader_files( $default_file ) {
        $templates   = [];
        $templates[] = 'woocommerce.php';

        if ( is_page_template() ) {
            $page_template = get_page_template_slug();

            if ( $page_template ) {
                $validated_file = validate_file( $page_template );
                if ( 0 === $validated_file ) {
                    $templates[] = $page_template;
                } else {
                    error_log( "WooCommerce: Unable to validate template path: \"$page_template\". Error Code: $validated_file." ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                }
            }
        }

        if ( is_singular( 'product' ) ) {
            $object       = get_queried_object();
            $name_decoded = urldecode( $object->post_name );
            if ( $name_decoded !== $object->post_name ) {
                $templates[] = "single-product-{$name_decoded}.php";
            }
            $templates[] = "single-product-{$object->post_name}.php";
        }

        if ( is_product_taxonomy() ) {
            $object = get_queried_object();

            $templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
            //$templates[] = self::$theme_customisations_dir . 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
            $templates[] = 'taxonomy-' . $object->taxonomy . '.php';
            //$templates[] = self::$theme_customisations_dir . 'taxonomy-' . $object->taxonomy . '.php';

            if ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) {
                $cs_taxonomy = str_replace( '_', '-', $object->taxonomy );
                $cs_default  = str_replace( '_', '-', $default_file );
                $templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
                //$templates[] = self::$theme_customisations_dir . 'taxonomy-' . $cs_taxonomy . '-' . $object->slug . '.php';
                $templates[] = 'taxonomy-' . $object->taxonomy . '.php';
                //$templates[] = self::$theme_customisations_dir . 'taxonomy-' . $cs_taxonomy . '.php';
                $templates[] = $cs_default;
            }
        }

        $templates[] = $default_file;
        if ( isset( $cs_default ) ) {
            $templates[] = self::$theme_customisations_dir . $cs_default;
        }
        $templates[] = self::$theme_customisations_dir . $default_file;

        return array_unique( $templates );
    }
}

add_action( 'init', array( 'WC_Theme_Customisations_Template_Loader', 'init' ), 999 );
