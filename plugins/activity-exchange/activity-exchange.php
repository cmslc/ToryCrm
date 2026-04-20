<?php
/**
 * Plugin Name: Trao đổi & Bình luận
 * Plugin URI: https://torycrm.com
 * Description: Thêm phần trao đổi, bình luận (like/dislike/reply/file/@mention) vào chi tiết KH, báo giá, đơn hàng, hợp đồng
 * Version: 1.0.0
 * Author: ToryCRM
 * Author URI: https://torycrm.com
 * Slug: activity-exchange
 * Icon: ri-chat-3-line
 * Category: Tính năng
 * Modules: contacts, quotations, orders, contracts, deals
 */

if (!defined('BASE_PATH')) return;

/**
 * Render activity exchange feed for any entity.
 *
 * Usage in any view:
 *   <?php activity_exchange_render('quotation', $quotation['id']); ?>
 *
 * @param string $entityType  contact|quotation|order|contract|deal
 * @param int    $entityId
 */
function activity_exchange_render(string $entityType, int $entityId): void
{
    if (!function_exists('plugin_active') || !plugin_active('activity-exchange')) return;

    $userId = $_SESSION['user']['id'] ?? 0;
    $activities = \App\Services\ActivityExchangeService::getActivities($entityType, $entityId, $userId);
    $allUsers = \App\Services\ActivityExchangeService::getAllUsers();

    // Variables for the view
    $entityType = $entityType;
    $entityId = $entityId;

    include __DIR__ . '/views/feed.php';
}
