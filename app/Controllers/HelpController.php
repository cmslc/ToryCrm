<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class HelpController extends Controller
{
    public function index()
    {
        $categories = Database::fetchAll(
            "SELECT hc.*, COUNT(ha.id) as article_count
             FROM help_categories hc
             LEFT JOIN help_articles ha ON ha.category_id = hc.id AND ha.is_published = 1
             GROUP BY hc.id
             ORDER BY hc.sort_order ASC, hc.name ASC"
        );

        return $this->view('help.index', [
            'categories' => $categories,
        ]);
    }

    public function category($slug)
    {
        $category = Database::fetch(
            "SELECT * FROM help_categories WHERE slug = ?",
            [$slug]
        );

        if (!$category) {
            return $this->redirect('help');
        }

        $articles = Database::fetchAll(
            "SELECT * FROM help_articles WHERE category_id = ? AND is_published = 1 ORDER BY sort_order ASC, created_at DESC",
            [$category['id']]
        );

        return $this->view('help.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }

    public function article($slug)
    {
        $article = Database::fetch(
            "SELECT ha.*, hc.name as category_name, hc.slug as category_slug
             FROM help_articles ha
             JOIN help_categories hc ON ha.category_id = hc.id
             WHERE ha.slug = ?",
            [$slug]
        );

        if (!$article) {
            return $this->redirect('help');
        }

        // Increment view count
        Database::query(
            "UPDATE help_articles SET view_count = view_count + 1 WHERE id = ?",
            [$article['id']]
        );

        // Related articles from same category
        $relatedArticles = Database::fetchAll(
            "SELECT id, title, slug, created_at FROM help_articles
             WHERE category_id = ? AND id != ? AND is_published = 1
             ORDER BY view_count DESC LIMIT 5",
            [$article['category_id'], $article['id']]
        );

        return $this->view('help.article', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function search()
    {
        $q = trim($this->input('q') ?? '');
        $results = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $results = Database::fetchAll(
                "SELECT ha.*, hc.name as category_name, hc.slug as category_slug
                 FROM help_articles ha
                 JOIN help_categories hc ON ha.category_id = hc.id
                 WHERE ha.is_published = 1 AND (ha.title LIKE ? OR ha.content LIKE ?)
                 ORDER BY ha.view_count DESC
                 LIMIT 50",
                [$like, $like]
            );
        }

        return $this->view('help.search', [
            'query' => $q,
            'results' => $results,
        ]);
    }

    public function helpful($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('help');
        }

        $vote = $this->input('vote');
        $article = Database::fetch("SELECT * FROM help_articles WHERE id = ?", [$id]);

        if (!$article) {
            return $this->json(['error' => 'Not found'], 404);
        }

        if ($vote === 'yes') {
            Database::query("UPDATE help_articles SET helpful_yes = helpful_yes + 1 WHERE id = ?", [$id]);
        } else {
            Database::query("UPDATE help_articles SET helpful_no = helpful_no + 1 WHERE id = ?", [$id]);
        }

        $this->setFlash('success', 'Cam on phan hoi cua ban!');
        return $this->back();
    }
}
