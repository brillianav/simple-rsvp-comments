<?php
/**
 * Plugin Name: Simple RSVP Comments
 * Plugin URI: https://brillianav.com
 * Description: Form RSVP dengan daftar ucapan yang muncul langsung tanpa refresh halaman. Gunakan shortcode [simple_rsvp_comments].
 * Version: 1.0.7
 * Author: Brillian
 * Text Domain: simple-rsvp-comments
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simple_RSVP_Comments {

    const VERSION = '1.0.7';
    const POST_TYPE = 'simple_rsvp_entry';
    const NONCE_ACTION = 'simple_rsvp_nonce_action';
    const NONCE_NAME = 'simple_rsvp_nonce';

    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_shortcode('simple_rsvp_comments', [$this, 'render_shortcode']);
        add_shortcode('rsvp_form', [$this, 'render_shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'register_assets']);

        add_action('wp_ajax_simple_rsvp_submit', [$this, 'handle_submit']);
        add_action('wp_ajax_nopriv_simple_rsvp_submit', [$this, 'handle_submit']);

        add_action('wp_ajax_simple_rsvp_load', [$this, 'handle_load']);
        add_action('wp_ajax_nopriv_simple_rsvp_load', [$this, 'handle_load']);

        add_action('add_meta_boxes', [$this, 'add_rsvp_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_rsvp_meta_box']);

        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'render_admin_columns'], 10, 2);
    }

    public static function activate() {
        $instance = new self();
        $instance->register_post_type();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => __('RSVP Entries', 'simple-rsvp-comments'),
                'singular_name' => __('RSVP Entry', 'simple-rsvp-comments'),
                'menu_name'     => __('RSVP Entries', 'simple-rsvp-comments'),
                'add_new_item'  => __('Add New RSVP', 'simple-rsvp-comments'),
                'edit_item'     => __('Edit RSVP', 'simple-rsvp-comments'),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-format-chat',
            'supports'     => ['title', 'editor'],
        ]);
    }

    public function register_assets() {
        $plugin_url = plugin_dir_url(__FILE__);
        $plugin_path = plugin_dir_path(__FILE__);

        wp_register_style(
            'simple-rsvp-comments',
            $plugin_url . 'assets/simple-rsvp-comments.css',
            [],
            filemtime($plugin_path . 'assets/simple-rsvp-comments.css')
        );

        wp_register_script(
            'simple-rsvp-comments',
            $plugin_url . 'assets/simple-rsvp-comments.js',
            ['jquery'],
            filemtime($plugin_path . 'assets/simple-rsvp-comments.js'),
            true
        );

        wp_localize_script('simple-rsvp-comments', 'SimpleRSVPComments', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce(self::NONCE_ACTION),
        ]);
    }

    private function get_to_name_value() {
        $to_name = '';

        if (shortcode_exists('to_name')) {
            $to_name = do_shortcode('[to_name]');
        }

        $to_name = wp_strip_all_tags($to_name);
        $to_name = trim($to_name);

        return $to_name;
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'per_page' => 5,
            'title'    => 'RSVP',
        ], $atts, 'simple_rsvp_comments');

        wp_enqueue_style('simple-rsvp-comments');
        wp_enqueue_script('simple-rsvp-comments');

        $per_page = absint($atts['per_page']);
        if ($per_page < 1) {
            $per_page = 5;
        }

        $to_name = $this->get_to_name_value();
        $name_id = wp_unique_id('src-rsvp-name-');

        ob_start();
        ?>
        <div class="src-rsvp" data-per-page="<?php echo esc_attr($per_page); ?>">

            <form class="src-rsvp__form" autocomplete="off">
                <div class="src-rsvp__field">
                    <label for="<?php echo esc_attr($name_id); ?>">Nama</label>
                    <input
                        id="<?php echo esc_attr($name_id); ?>"
                        type="text"
                        name="name"
                        placeholder="Nama Tamu"
                        value="<?php echo esc_attr($to_name); ?>"
                        required
                    >
                </div>

                <div class="src-rsvp__field">
                    <label>Kehadiran</label>
                    <select name="attendance" required>
                        <option value="">Kehadiran</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Tidak Hadir">Tidak Hadir</option>
                        <option value="Masih Ragu">Masih Ragu</option>
                    </select>
                </div>

                <div class="src-rsvp__field">
                    <label>Komentar atau Ucapan</label>
                    <textarea name="message" placeholder="Komentar atau Ucapan" required></textarea>
                </div>

                <button type="submit" class="src-rsvp__button">Kirim</button>
                <div class="src-rsvp__alert" aria-live="polite"></div>
            </form>

            <div class="src-rsvp__divider"></div>

            <div class="src-rsvp__list" aria-live="polite"></div>
            <div class="src-rsvp__pagination" aria-label="Pagination komentar"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_submit() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $name       = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $attendance = sanitize_text_field(wp_unslash($_POST['attendance'] ?? ''));
        $message    = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

        $allowed_attendance = ['Hadir', 'Tidak Hadir', 'Masih Ragu'];

        if (empty($name) || empty($attendance) || empty($message)) {
            wp_send_json_error(['message' => 'Mohon isi nama, kehadiran, dan komentar.']);
        }

        if (!in_array($attendance, $allowed_attendance, true)) {
            wp_send_json_error(['message' => 'Pilihan kehadiran tidak valid.']);
        }

        $post_id = wp_insert_post([
            'post_type'    => self::POST_TYPE,
            'post_title'   => $name,
            'post_content' => $message,
            'post_status'  => 'publish',
        ], true);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'RSVP gagal disimpan. Silakan coba lagi.']);
        }

        update_post_meta($post_id, '_src_attendance', $attendance);

        wp_send_json_success([
            'message' => 'Terima kasih, RSVP berhasil dikirim.',
        ]);
    }

    public function handle_load() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $page = absint($_POST['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $per_page = absint($_POST['per_page'] ?? 5);
        if ($per_page < 1) {
            $per_page = 5;
        }

        $query = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        ob_start();

        if (!$query->have_posts()) {
            echo '<div class="src-rsvp__empty">Belum ada ucapan.</div>';
        }

        while ($query->have_posts()) {
            $query->the_post();

            $entry_id   = get_the_ID();
            $name       = get_the_title();
            $message    = get_the_content();
            $attendance = get_post_meta($entry_id, '_src_attendance', true);
            $date       = get_the_date('j F Y \a\t H.i');
            $initials   = $this->get_initials($name);
            ?>
            <div class="src-rsvp__card">
                <div class="src-rsvp__avatar" aria-hidden="true">
                    <?php echo esc_html($initials); ?>
                </div>

                <div class="src-rsvp__content">
                    <div class="src-rsvp__name-row">
                        <strong><?php echo esc_html($name); ?></strong>

                        <?php if (!empty($attendance)) : ?>
                            <span class="src-rsvp__badge"><?php echo esc_html($attendance); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="src-rsvp__message">
                        <?php echo nl2br(esc_html($message)); ?>
                    </div>

                    <div class="src-rsvp__date">
                        <?php echo esc_html($date); ?>
                    </div>
                </div>
            </div>
            <?php
        }

        wp_reset_postdata();

        $html = ob_get_clean();

        $total_pages = (int) $query->max_num_pages;

        wp_send_json_success([
            'html'        => $html,
            'page'        => $page,
            'perPage'     => $per_page,
            'totalPages'  => $total_pages,
            'totalItems'  => (int) $query->found_posts,
        ]);
    }

    public function add_rsvp_meta_boxes() {
        add_meta_box(
            'src_rsvp_details',
            __('Detail RSVP', 'simple-rsvp-comments'),
            [$this, 'render_rsvp_meta_box'],
            self::POST_TYPE,
            'side',
            'default'
        );
    }

    public function render_rsvp_meta_box($post) {
        wp_nonce_field('src_rsvp_meta_box_action', 'src_rsvp_meta_box_nonce');

        $attendance = get_post_meta($post->ID, '_src_attendance', true);

        $options = [
            'Hadir'       => 'Hadir',
            'Tidak Hadir' => 'Tidak Hadir',
            'Masih Ragu'  => 'Masih Ragu',
        ];
        ?>
        <p>
            <label for="src-rsvp-attendance-admin">
                <strong><?php esc_html_e('Kehadiran', 'simple-rsvp-comments'); ?></strong>
            </label>
        </p>

        <select
            id="src-rsvp-attendance-admin"
            name="src_rsvp_attendance"
            style="width: 100%;"
        >
            <option value="">Pilih Kehadiran</option>

            <?php foreach ($options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($attendance, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function save_rsvp_meta_box($post_id) {
        if (
            !isset($_POST['src_rsvp_meta_box_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['src_rsvp_meta_box_nonce'])),
                'src_rsvp_meta_box_action'
            )
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $attendance = sanitize_text_field(wp_unslash($_POST['src_rsvp_attendance'] ?? ''));

        $allowed_attendance = ['Hadir', 'Tidak Hadir', 'Masih Ragu'];

        if ($attendance && in_array($attendance, $allowed_attendance, true)) {
            update_post_meta($post_id, '_src_attendance', $attendance);
        } else {
            delete_post_meta($post_id, '_src_attendance');
        }
    }

    public function add_admin_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;

            if ($key === 'title') {
                $new_columns['src_attendance'] = __('Kehadiran', 'simple-rsvp-comments');
            }
        }

        return $new_columns;
    }

    public function render_admin_columns($column, $post_id) {
        if ($column === 'src_attendance') {
            $attendance = get_post_meta($post_id, '_src_attendance', true);

            if ($attendance) {
                echo esc_html($attendance);
            } else {
                echo '<span style="color:#777;">-</span>';
            }
        }
    }

    private function get_initials($name) {
        $name = trim(wp_strip_all_tags($name));

        if ($name === '') {
            return '?';
        }

        $words = preg_split('/\s+/', $name);
        $words = array_filter($words);
        $words = array_slice($words, 0, 3);
        $initials = '';

        foreach ($words as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return strtoupper($initials);
    }

}

register_activation_hook(__FILE__, ['Simple_RSVP_Comments', 'activate']);
register_deactivation_hook(__FILE__, ['Simple_RSVP_Comments', 'deactivate']);

new Simple_RSVP_Comments();
