<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Elementor_Secure_Video extends \Elementor\Widget_Base {

    public function get_name() {
        return 'secure-video';
    }

    public function get_title() {
        return __( 'Secure Video', 'esvw-widget' );
    }

    public function get_icon() {
        return 'eicon-video-playlist';
    }

    public function get_categories() {
        return [ 'basic', 'general' ];
    }

    protected function register_controls() {
        // 1) Video Settings
        $this->start_controls_section(
            'video_settings_section',
            [
                'label' => __( 'Video Settings', 'esvw-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'video_file',
            [
                'label'       => __( 'Video File', 'esvw-widget' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'media_types' => [ 'video' ],
                'default'     => [ 'url' => '' ],
            ]
        );

        $default_width  = get_option( 'esvw_default_width', 500 );
        $default_height = get_option( 'esvw_default_height', 260 );

        $this->add_control(
            'width',
            [
                'label'   => __( 'Video Width', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_width,
            ]
        );
        $this->add_control(
            'height',
            [
                'label'   => __( 'Video Height', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_height,
            ]
        );

        $this->add_control(
            'poster_image',
            [
                'label'   => __( 'Poster Image', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [ 'url' => '' ],
            ]
        );

        $this->end_controls_section();

        // 2) Title & Description
        $this->start_controls_section(
            'title_description_section',
            [
                'label' => __( 'Title & Description', 'esvw-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Title
        $this->add_control(
            'title_text',
            [
                'label'       => __( 'Title', 'esvw-widget' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __( 'Enter video title', 'esvw-widget' ),
            ]
        );
        $this->add_control(
            'title_font_size',
            [
                'label'   => __( 'Title Font Size (px)', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min'     => 1,
                'max'     => 100,
            ]
        );
        $this->add_control(
            'title_color',
            [
                'label'   => __( 'Title Color', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
            ]
        );

        // Description
        $this->add_control(
            'description_text',
            [
                'label'       => __( 'Description', 'esvw-widget' ),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'default'     => '',
                'placeholder' => __( 'Enter video description', 'esvw-widget' ),
            ]
        );
        $this->add_control(
            'description_font_size',
            [
                'label'   => __( 'Description Font Size (px)', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 14,
                'min'     => 1,
                'max'     => 100,
            ]
        );
        $this->add_control(
            'description_color',
            [
                'label'   => __( 'Description Color', 'esvw-widget' ),
                'type'    => \Elementor\Controls_Manager::COLOR,
                'default' => '#555555',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // If no video selected, bail out
        if ( empty( $settings['video_file']['url'] ) ) {
            echo '<p style="color:red;">No video file selected.</p>';
            return;
        }

        // 1) Grab the expiring URL settings from WP options
        $secret_key         = get_option( 'esvw_secret_key', 'mySuperSecretKey123' );
        $protected_base_url = get_option( 'esvw_protected_base_url', 'https://contactcustody.kcdev.site/ppv/' );
        $expiry_seconds     = get_option( 'esvw_expiry_seconds', 3600 );

        // 2) Build the expiring link
        $expires   = time() + (int) $expiry_seconds;
        $uploadUrl = $settings['video_file']['url'];
        $fileName  = basename( parse_url( $uploadUrl, PHP_URL_PATH ) ); // e.g. hashedName.mp4

        $token     = md5( $secret_key . $fileName . $expires );
        $video_url = $protected_base_url . $fileName . '?st=' . $token . '&e=' . $expires;

        // 3) Title/Description styling
        $title_style = sprintf(
            'font-size:%dpx;color:%s;margin:0 0 10px;',
            (int) $settings['title_font_size'],
            esc_attr( $settings['title_color'] )
        );
        $desc_style = sprintf(
            'font-size:%dpx;color:%s;margin:0 0 20px;',
            (int) $settings['description_font_size'],
            esc_attr( $settings['description_color'] )
        );

        // 4) Poster Image
        $poster_url = '';
        if ( ! empty( $settings['poster_image']['url'] ) ) {
            $poster_url = $settings['poster_image']['url'];
        }

        // 5) Output the HTML
        ?>
        <div class="esvw-secure-video-widget" style="position:relative;">
            <?php if ( ! empty( $settings['title_text'] ) ) : ?>
                <h2 style="<?php echo esc_attr( $title_style ); ?>">
                    <?php echo esc_html( $settings['title_text'] ); ?>
                </h2>
            <?php endif; ?>

            <?php if ( ! empty( $settings['description_text'] ) ) : ?>
                <p style="<?php echo esc_attr( $desc_style ); ?>">
                    <?php echo nl2br( esc_html( $settings['description_text'] ) ); ?>
                </p>
            <?php endif; ?>

            <video
                width="<?php echo esc_attr( $settings['width'] ); ?>"
                height="<?php echo esc_attr( $settings['height'] ); ?>"
                controls
                controlsList="nodownload"
                disablePictureInPicture
                oncontextmenu="return false;"
                poster="<?php echo esc_url( $poster_url ); ?>"
            >
                <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
                <?php esc_html_e( 'Your browser does not support the video tag.', 'esvw-widget' ); ?>
            </video>
        </div>
        <?php
    }
}