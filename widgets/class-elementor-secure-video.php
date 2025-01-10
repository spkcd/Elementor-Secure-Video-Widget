<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Elementor_Secure_Video extends \Elementor\Widget_Base {

    public function get_name() {
        return 'secure-video';
    }

    public function get_title() {
        return __( 'Secure Video', 'esv-widget' );
    }

    public function get_icon() {
        return 'eicon-video-playlist';
    }

    public function get_categories() {
        return [ 'basic', 'general' ];
    }

    protected function register_controls() {
        // CONTENT Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Secure Video Settings', 'esv-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // VIDEO FILE
        $this->add_control(
            'video_file',
            [
                'label'       => __( 'Video File', 'esv-widget' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'media_types' => [ 'video' ],
                'default'     => [ 'url' => '' ],
            ]
        );

        // DEFAULT WIDTH & HEIGHT (from plugin settings)
        $default_width  = get_option( 'esv_default_width', 500 );
        $default_height = get_option( 'esv_default_height', 260 );

        $this->add_control(
            'width',
            [
                'label'   => __( 'Video Width', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_width,
            ]
        );

        $this->add_control(
            'height',
            [
                'label'   => __( 'Video Height', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_height,
            ]
        );

        $this->end_controls_section();

        // TITLE & DESCRIPTION Section
        $this->start_controls_section(
            'title_description_section',
            [
                'label' => __( 'Title & Description', 'esv-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // TITLE
        $this->add_control(
            'title_text',
            [
                'label'       => __( 'Video Title', 'esv-widget' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __( 'Enter your title here', 'esv-widget' ),
                'default'     => '',
            ]
        );

        $this->add_control(
            'title_font_size',
            [
                'label'   => __( 'Title Font Size (px)', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min'     => 1,
                'max'     => 100,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Title Color', 'esv-widget' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#000000',
            ]
        );

        // DESCRIPTION
        $this->add_control(
            'description_text',
            [
                'label'       => __( 'Video Description', 'esv-widget' ),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => __( 'Enter your description here', 'esv-widget' ),
                'default'     => '',
            ]
        );

        $this->add_control(
            'description_font_size',
            [
                'label'   => __( 'Description Font Size (px)', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 14,
                'min'     => 1,
                'max'     => 100,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label'     => __( 'Description Color', 'esv-widget' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#555555',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['video_file']['url'] ) ) {
            echo '<p style="color:red;">No video file selected.</p>';
            return;
        }

        // 1. Pull expiring URL config from plugin options
        $secret_key         = get_option( 'esv_secret_key', 'mySuperSecretKey123' );
        $protected_base_url = get_option( 'esv_protected_base_url', 'https://contactcustody.kcdev.site/ppv/' );
        $expiry_seconds     = get_option( 'esv_expiry_seconds', 3600 );

        // 2. Build the expiring URL
        $expires    = time() + (int) $expiry_seconds;
        $upload_url = $settings['video_file']['url'];
        $file_name  = basename( parse_url( $upload_url, PHP_URL_PATH ) ); // e.g., "myvideo.mp4"

        $token     = md5( $secret_key . $file_name . $expires );
        $video_url = $protected_base_url . $file_name . '?st=' . $token . '&e=' . $expires;

        // 3. Title & Description styling
        //    We can inline style them for simplicity. Or add classes and use external CSS.
        $title_style = sprintf(
            'font-size: %dpx; color: %s; margin: 0 0 10px;',
            absint( $settings['title_font_size'] ),
            esc_attr( $settings['title_color'] )
        );

        $description_style = sprintf(
            'font-size: %dpx; color: %s; margin: 0 0 20px;',
            absint( $settings['description_font_size'] ),
            esc_attr( $settings['description_color'] )
        );

        // 4. Output the HTML
        ?>
        <div class="esv-secure-video-widget">
            <?php if ( ! empty( $settings['title_text'] ) ) : ?>
                <h2 style="<?php echo esc_attr( $title_style ); ?>">
                    <?php echo esc_html( $settings['title_text'] ); ?>
                </h2>
            <?php endif; ?>

            <?php if ( ! empty( $settings['description_text'] ) ) : ?>
                <p style="<?php echo esc_attr( $description_style ); ?>">
                    <?php echo nl2br( esc_html( $settings['description_text'] ) ); ?>
                </p>
            <?php endif; ?>

            <video
                width="<?php echo esc_attr( $settings['width'] ); ?>"
                height="<?php echo esc_attr( $settings['height'] ); ?>"
                controls
            >
                <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
                <?php esc_html_e( 'Your browser does not support the video tag.', 'esv-widget' ); ?>
            </video>
        </div>
        <?php
    }
}