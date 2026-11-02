<?php
$template = json_decode(file_get_contents('/setup/elementor/healthcare-clinic-landing-page.json'), true, 512, JSON_THROW_ON_ERROR);
if (count($template['content'] ?? []) !== 14) {
    throw new RuntimeException('Clinic template is incomplete');
}
$page = get_page_by_path('health-checkup-clinic');
if ($page) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $page->ID);
    return;
}
$id = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_title' => 'Health Checkup Clinic',
    'post_name' => 'health-checkup-clinic',
], true);
if (is_wp_error($id)) {
    throw new RuntimeException($id->get_error_message());
}
update_post_meta($id, '_elementor_edit_mode', 'builder');
update_post_meta($id, '_elementor_data', wp_slash(wp_json_encode($template['content'])));
update_post_meta($id, '_wp_page_template', 'elementor_canvas');
update_option('show_on_front', 'page');
update_option('page_on_front', $id);
if ((int) get_option('page_on_front') !== (int) $id) {
    throw new RuntimeException('Clinic homepage was not set');
}
