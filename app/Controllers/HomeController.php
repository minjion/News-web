<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

class HomeController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;
        $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

        // Lay danh sach bai viet (neu co bo loc theo category)
        $articleModel = new ArticleModel();
        $featured = $articleModel->getFeaturedArticle($catId > 0 ? $catId : null);

        [$articles, $total] = $articleModel->getPublishedArticles(
            $page,
            $per,
            $catId > 0 ? $catId : null
        );

        // Loai tru bai noi bat khoi danh sach bai moi de tranh trung lap
        if ($featured && $page === 1) {
            $articles = array_values(array_filter($articles, function ($a) use ($featured) {
                return (int)$a['article_id'] !== (int)$featured['article_id'];
            }));
        }

        $pages = (int)ceil($total / $per);

        // Lay danh sach danh muc de hien thi trong menu
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->listAll();

        // Bai doc nhieu (7 ngay gan nhat)
        $trending = $articleModel->getTrendingArticles(5, $catId > 0 ? $catId : null);

        // Gui du lieu sang view
        $this->view('home/index', [
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages,
            'categories' => $categories,
            'selectedCat' => $catId,
            'featured' => $featured,
            'trending' => $trending,
        ]);
    }

    public function categories(): void
    {
        $categoryModel = new CategoryModel();
        $rows = $categoryModel->listWithTotals();

        $this->view('home/categories', [
            'rows' => $rows
        ]);
    }
}
