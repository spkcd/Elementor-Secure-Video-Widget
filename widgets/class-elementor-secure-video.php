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
        // Choose an appropriate icon from Elementor's icon library
        return 'eicon-video-playlist';
    }

    public function get_categories() {
        // Choose which category(ies) your widget belongs in
        return [ 'basic', 'general' ];
    }

    protected function register_controls() {
        // Start a control section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Secure Video Settings', 'esv-widget' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Video File Control (via WP media library)
        $this->add_control(
            'video_file',
            [
                'label'       => __( 'Video File', 'esv-widget' ),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'media_types' => [ 'video' ],
                'default'     => [
                    'url' => '',
                ],
            ]
        );

        // Width
        $this->add_control(
            'width',
            [
                'label'   => __( 'Video Width', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 640,
            ]
        );

        // Height
        $this->add_control(
            'height',
            [
                'label'   => __( 'Video Height', 'esv-widget' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 360,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        // Retrieve widget settings
        $settings = $this->get_settings_for_display();

        // If no video chosen, we can bail or show a notice
        if ( empty( $settings['video_file']['url'] ) ) {
            echo '<p style="color:red;">No video file selected. Please choose a file in the widget settings.</p>';
            return;
        }

        /**
         * 1. Define your SECRET KEY and PROTECTED URL
         *    Must match your Apache check_access.php logic.
         */
        $secret_key         = 'mySuperSecretKey123';
        $protected_base_url = 'https://contactcustody.kcdev.site/ppv/';

        // 2. Set expiry (e.g., 1 hour)
        $expires = time() + 3600;

        // 3. Parse the file name from the user-selected URL
        //    For this example, we'll just take the filename from the path.
        //    IMPORTANT: This only works if the file physically resides in /ppv/.
        $upload_url  = $settings['video_file']['url']; // e.g. "https://yoursite.com/wp-content/uploads/..."
        $file_name   = basename( parse_url( $upload_url, PHP_URL_PATH ) );

        // 4. Compute the token (hash)
        $token = md5( $secret_key . $file_name . $expires );

        // 5. Construct the final expiring URL
        //    e.g. "https://contactcustody.kcdev.site/ppv/<file_name>?st=abc123&e=1700000000"
        $video_url = $protected_base_url . $file_name . '?st=' . $token . '&e=' . $expires;

        // 6. Output the HTML5 <video> tag
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