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
        return 'eicon-video-playlist'; // or choose another icon
    }

    public function get_categories() {
        return [ 'basic', 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Secure Video Settings', 'esv-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // 1. Video File
        $this->add_control(
            'video_file',
            [
                'label'       => __( 'Video File', 'esv-widget' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'media_types' => [ 'video' ],
                'default'     => [ 'url' => '' ],
            ]
        );

        // 2. Width
        $default_width = get_option( 'esv_default_width', 500 );
        $this->add_control(
            'width',
            [
                'label'   => __( 'Video Width', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_width,
            ]
        );

        // 3. Height
        $default_height = get_option( 'esv_default_height', 260 );
        $this->add_control(
            'height',
            [
                'label'   => __( 'Video Height', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => $default_height,
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

        // Define your secret key and protected base URL
        $secret_key         = 'mySuperSecretKey123';
        $protected_base_url = 'https://contactcustody.kcdev.site/ppv/';

        // Build expiring URL
        $expires = time() + 3600;
        $upload_url = $settings['video_file']['url'];
        $file_name  = basename( parse_url( $upload_url, PHP_URL_PATH ) );

        $token = md5( $secret_key . $file_name . $expires );
        $video_url = $protected_base_url . $file_name . '?st=' . $token . '&e=' . $expires;

        // Output the HTML5 <video> tag
        ?>
        <video
            width="<?php echo esc_attr( $settings['width'] ); ?>"
            height="<?php echo esc_attr( $settings['height'] ); ?>"
            controls
        >
            <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
            <?php esc_html_e( 'Your browser does not support the video tag.', 'esv-widget' ); ?>
        </video>
        <?php
    }
}