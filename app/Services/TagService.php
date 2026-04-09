<?php

namespace App\Services;

use Core\Database;

class TagService
{
    /**
     * Get all tags ordered by use_count DESC
     */
    public static function getAll(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT * FROM tags WHERE tenant_id = ? ORDER BY use_count DESC, name ASC",
            [$tenantId]
        );
    }

    /**
     * Get tags for an entity (JOIN taggables)
     */
    public static function getForEntity(string $entityType, int $entityId): array
    {
        return Database::fetchAll(
            "SELECT t.* FROM tags t
             JOIN taggables tg ON t.id = tg.tag_id
             WHERE tg.entity_type = ? AND tg.entity_id = ?
             ORDER BY t.name ASC",
            [$entityType, $entityId]
        );
    }

    /**
     * Sync tags for an entity: delete existing taggables, insert new ones, update use_count
     */
    public static function syncTags(string $entityType, int $entityId, array $tagIds): void
    {
        // Get current tag IDs for this entity
        $currentTagIds = array_column(
            Database::fetchAll(
                "SELECT tag_id FROM taggables WHERE entity_type = ? AND entity_id = ?",
                [$entityType, $entityId]
            ),
            'tag_id'
        );

        // Delete existing taggables
        Database::query(
            "DELETE FROM taggables WHERE entity_type = ? AND entity_id = ?",
            [$entityType, $entityId]
        );

        // Decrement use_count for removed tags
        if (!empty($currentTagIds)) {
            $placeholders = implode(',', array_fill(0, count($currentTagIds), '?'));
            Database::query(
                "UPDATE tags SET use_count = GREATEST(0, use_count - 1) WHERE id IN ({$placeholders})",
                $currentTagIds
            );
        }

        // Insert new taggables and increment use_count
        foreach ($tagIds as $tagId) {
            $tagId = (int) $tagId;
            if ($tagId <= 0) continue;

            Database::insert('taggables', [
                'tag_id' => $tagId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);

            Database::query(
                "UPDATE tags SET use_count = use_count + 1 WHERE id = ?",
                [$tagId]
            );
        }
    }

    /**
     * Create a new tag
     */
    public static function createTag(string $name, string $color, int $tenantId): int
    {
        return Database::insert('tags', [
            'name' => $name,
            'color' => $color,
            'tenant_id' => $tenantId,
            'use_count' => 0,
        ]);
    }

    /**
     * Delete tag and all taggables
     */
    public static function deleteTag(int $tagId): void
    {
        Database::query("DELETE FROM taggables WHERE tag_id = ?", [$tagId]);
        Database::query("DELETE FROM tags WHERE id = ?", [$tagId]);
    }

    /**
     * Search tags by name for autocomplete
     */
    public static function search(string $query, int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT * FROM tags WHERE tenant_id = ? AND name LIKE ? ORDER BY use_count DESC LIMIT 20",
            [$tenantId, '%' . $query . '%']
        );
    }
}
