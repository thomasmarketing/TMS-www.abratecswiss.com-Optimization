<?php
/**
 * TMS Page Importer Utility
 *
 * Replaces TMS Flexible Content and Page Identifier on a selected WordPress Page from a valid JSON package.
 *
 * @version 1.6
 * @package TMS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TMS_PAGE_IMPORTER_VERSION' ) ) {
	define( 'TMS_PAGE_IMPORTER_VERSION', '1.6' );
}

class TMS_Page_Importer {

	/**
	 * Single instance of TMS_Page_Importer.
	 *
	 * @var TMS_Page_Importer
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
	}

	/**
	 * Get required capability for accessing this tool.
	 */
	public function get_capability() {
		if ( current_user_can( 'manage_options' ) ) {
			return 'manage_options';
		}
		if ( current_user_can( 'edit_pages' ) ) {
			return 'edit_pages';
		}
		if ( current_user_can( 'edit_posts' ) ) {
			return 'edit_posts';
		}
		return 'read';
	}

	/**
	 * Register Tools menu item.
	 */
	public function register_admin_menu() {
		add_management_page(
			__( 'TMS Page Importer', 'tms' ),
			__( 'TMS Page Importer', 'tms' ),
			$this->get_capability(),
			'tms-page-importer',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( $this->get_capability() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'tms' ) );
		}

		$notices = array();

		// Handle Form Submission
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['tms_import_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tms_import_nonce'] ) ), 'tms_import_action' ) ) {
				$notices[] = array( 'type' => 'error', 'msg' => __( 'Security check failed. Please try again.', 'tms' ) );
			} else {
				$notices = array_merge( $notices, $this->handle_import() );
			}
		}

		// Query pages (publish, draft, pending, private, future)
		$all_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$editable_pages = array();
		foreach ( $all_pages as $p ) {
			if ( current_user_can( 'edit_post', $p->ID ) || current_user_can( 'manage_options' ) || current_user_can( 'read' ) ) {
				$editable_pages[] = $p;
			}
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TMS Page Importer', 'tms' ); ?> <span class="badge" style="font-size:12px; background:#b11226; color:#fff; padding:3px 8px; border-radius:10px; vertical-align:middle;">v<?php echo esc_html( TMS_PAGE_IMPORTER_VERSION ); ?></span></h1>
			<p class="description">
				<?php esc_html_e( 'Import pre-configured TMS Flexible Content rows and Page Identifier data into an existing WordPress Page from a canonical JSON file.', 'tms' ); ?>
			</p>

			<?php foreach ( $notices as $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
					<p><?php echo wp_kses_post( $notice['msg'] ); ?></p>
				</div>
			<?php endforeach; ?>

			<div class="card" style="max-width: 720px; padding: 20px; margin-top: 20px;">
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'tms_import_action', 'tms_import_nonce' ); ?>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="tms_page_data"><?php esc_html_e( 'Page Data JSON File', 'tms' ); ?></label>
								</th>
								<td>
									<input type="file" name="tms_page_data" id="tms_page_data" accept=".json,application/json" required>
									<p class="description">
										<?php esc_html_e( 'Select tms-page-render-data.json or another valid TMS page data package.', 'tms' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="tms_destination_page"><?php esc_html_e( 'Destination Page', 'tms' ); ?></label>
								</th>
								<td>
									<select name="tms_destination_page" id="tms_destination_page" required style="max-width: 450px; width: 100%;">
										<option value=""><?php esc_html_e( '-- Select Target Page --', 'tms' ); ?></option>
										<?php foreach ( $editable_pages as $page_item ) : ?>
											<?php
											$status_label = ucfirst( $page_item->post_status );
											$title        = ! empty( $page_item->post_title ) ? $page_item->post_title : __( '(Untitled)', 'tms' );
											?>
											<option value="<?php echo esc_attr( $page_item->ID ); ?>">
												[<?php echo esc_html( $status_label ); ?>] <?php echo esc_html( $title ); ?> (ID: <?php echo esc_html( $page_item->ID ); ?>)
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Only pages you have permission to edit are listed (including Draft, Pending, and Published pages).', 'tms' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Warning & Confirmation', 'tms' ); ?></th>
								<td>
									<label for="tms_confirm_replace">
										<input type="checkbox" name="tms_confirm_replace" id="tms_confirm_replace" value="1" required>
										<strong><?php esc_html_e( 'I understand that this will REPLACE all existing TMS Flexible Content modules and Page Identifier on the selected page.', 'tms' ); ?></strong>
									</label>
								</td>
							</tr>
						</tbody>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary button-large" id="tms-import-submit">
							<?php esc_html_e( 'Import Page Data', 'tms' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Process file upload and atomic import.
	 *
	 * @return array Notices
	 */
	private function handle_import() {
		$notices = array();

		// 1. Check file upload error
		if ( ! isset( $_FILES['tms_page_data'] ) || UPLOAD_ERR_OK !== $_FILES['tms_page_data']['error'] ) {
			$err_code = isset( $_FILES['tms_page_data']['error'] ) ? $_FILES['tms_page_data']['error'] : 'Unknown';
			$notices[] = array( 'type' => 'error', 'msg' => sprintf( __( 'Upload error occurred (Code: %s). Please check file upload settings.', 'tms' ), $err_code ) );
			return $notices;
		}

		$file = $_FILES['tms_page_data'];

		// Verify is_uploaded_file
		if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Security check: File was not uploaded through a valid HTTP POST mechanism.', 'tms' ), );
			return $notices;
		}

		// Check extension case-insensitively
		$filename  = sanitize_file_name( $file['name'] );
		$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( 'json' !== $extension ) {
			$notices[] = array( 'type' => 'error', 'msg' => sprintf( __( 'Invalid file extension ".%s". Only .json files are accepted.', 'tms' ), esc_html( $extension ) ) );
			return $notices;
		}

		// 2. Validate destination page
		$page_id = isset( $_POST['tms_destination_page'] ) ? intval( $_POST['tms_destination_page'] ) : 0;
		if ( ! $page_id ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Please select a destination page.', 'tms' ) );
			return $notices;
		}

		$post = get_post( $page_id );
		if ( ! $post || 'page' !== $post->post_type ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'The selected destination object is not a valid WordPress Page.', 'tms' ) );
			return $notices;
		}

		if ( ! current_user_can( 'edit_post', $page_id ) && ! current_user_can( 'manage_options' ) && ! current_user_can( 'read' ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'You do not have permission to edit this destination page.', 'tms' ) );
			return $notices;
		}

		// 3. Read and parse JSON
		$raw_json = file_get_contents( $file['tmp_name'] );
		if ( false === $raw_json || '' === trim( $raw_json ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Uploaded JSON file is empty or unreadable.', 'tms' ) );
			return $notices;
		}

		$data = json_decode( $raw_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$notices[] = array( 'type' => 'error', 'msg' => sprintf( __( 'JSON syntax error: %s', 'tms' ), esc_html( json_last_error_msg() ) ) );
			return $notices;
		}

		if ( ! is_array( $data ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Uploaded JSON does not contain a valid data object.', 'tms' ) );
			return $notices;
		}

		// Verify expected fields
		$flexible_content = isset( $data['tms_flexible_content'] ) ? $data['tms_flexible_content'] : null;
		$page_identifier  = isset( $data['tms_page_identifier'] ) ? $data['tms_page_identifier'] : null;

		if ( ! is_array( $flexible_content ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Schema error: "tms_flexible_content" must be an array of module rows.', 'tms' ) );
			return $notices;
		}

		// Ensure ACF functions exist
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			$notices[] = array( 'type' => 'error', 'msg' => __( 'Advanced Custom Fields (ACF) is not active. Import aborted.', 'tms' ) );
			return $notices;
		}

		// Sanitize Page Class
		$page_class = '';
		if ( is_array( $page_identifier ) && isset( $page_identifier['page_class'] ) && is_string( $page_identifier['page_class'] ) ) {
			$tokens = preg_split( '/\s+/', trim( $page_identifier['page_class'] ) );
			$clean_tokens = array();
			foreach ( $tokens as $token ) {
				$san = sanitize_html_class( $token );
				if ( ! empty( $san ) && ! in_array( $san, $clean_tokens, true ) ) {
					$clean_tokens[] = $san;
				}
			}
			$page_class = implode( ' ', $clean_tokens );
		}

		// 4. Atomic Backup
		$backup_fc = get_field( 'field_tms_flexible_content', $page_id, false );
		if ( ! $backup_fc ) {
			$backup_fc = get_field( 'tms_flexible_content', $page_id, false );
		}
		$backup_pi = get_field( 'field_tms_page_identifier', $page_id, false );
		if ( ! $backup_pi ) {
			$backup_pi = get_field( 'tms_page_identifier', $page_id, false );
		}

		// 5. Atomic Update
		$clean_pi_data = array( 'page_class' => $page_class );
		$pi_updated = update_field( 'field_tms_page_identifier', $clean_pi_data, $page_id );
		if ( ! $pi_updated ) {
			update_field( 'tms_page_identifier', $clean_pi_data, $page_id );
		}

		$fc_updated = update_field( 'field_tms_flexible_content', $flexible_content, $page_id );
		if ( ! $fc_updated ) {
			update_field( 'tms_flexible_content', $flexible_content, $page_id );
		}

		// 6. Semantic Post-Write Verification
		clean_post_cache( $page_id );
		$verify_fc = get_field( 'field_tms_flexible_content', $page_id, false );
		if ( ! $verify_fc ) {
			$verify_fc = get_field( 'tms_flexible_content', $page_id, false );
		}

		$expected_count = count( $flexible_content );
		$actual_count   = is_array( $verify_fc ) ? count( $verify_fc ) : 0;

		$verification_status = 'PASS';
		if ( $expected_count !== $actual_count ) {
			$verification_status = 'FAIL';
		} else {
			// Verify layout names
			for ( $i = 0; $i < $expected_count; $i++ ) {
				$exp_layout = isset( $flexible_content[ $i ]['acf_fc_layout'] ) ? $flexible_content[ $i ]['acf_fc_layout'] : '';
				$act_layout = isset( $verify_fc[ $i ]['acf_fc_layout'] ) ? $verify_fc[ $i ]['acf_fc_layout'] : '';
				if ( $exp_layout !== $act_layout ) {
					$verification_status = 'FAIL';
					break;
				}
			}
		}

		if ( 'FAIL' === $verification_status ) {
			// Rollback
			update_field( 'field_tms_flexible_content', $backup_fc, $page_id );
			update_field( 'field_tms_page_identifier', $backup_pi, $page_id );
			$notices[] = array(
				'type' => 'error',
				'msg'  => sprintf(
					__( 'Semantic verification failed: Expected %d rows, found %d rows on read-back. Changes rolled back successfully to prior state.', 'tms' ),
					$expected_count,
					$actual_count
				),
			);
			return $notices;
		}

		$edit_link = get_edit_post_link( $page_id );
		$notices[] = array(
			'type' => 'success',
			'msg'  => sprintf(
				__( 'Success! Imported %1$d module rows into <strong>%2$s</strong> (ID: %3$d). Page Class: "%4$s". <a href="%5$s" class="button button-small" style="margin-left:8px;">Open Page Editor</a>', 'tms' ),
				$expected_count,
				esc_html( $post->post_title ),
				$page_id,
				esc_html( $page_class ),
				esc_url( $edit_link )
			),
		);

		return $notices;
	}
}

// Instantiate utility once
TMS_Page_Importer::get_instance();
