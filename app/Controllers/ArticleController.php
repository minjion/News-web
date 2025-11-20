<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;

class ArticleController extends Controller
{
    public function show(int $id): void
    {
        $articleModel = new ArticleModel();
        $details = $articleModel->getByIdWithDetails($id);
        if (!$details) {
            http_response_code(404);
            echo 'Article not found';
            return;
        }
        
        // Ghi lai luot xem
        $userId = $_SESSION['user_id'] ?? null;
        $articleModel->addView($id, $userId);

        // Tinh thoi gian doc (gia su 220 tu/phut)
        $plain = trim(strip_tags($details['content'] ?? ''));
        $words = $plain === '' ? [] : preg_split('/\s+/u', $plain);
        $wordCount = is_array($words) ? count($words) : 0;
        $readingMinutes = max(1, (int)ceil($wordCount / 220));

        // Bai lien quan (cung danh muc)
        $related = [];
        $prevNext = ['prev' => null, 'next' => null];
        if (!empty($details['article']['category_id'])) {
            $catId = (int)$details['article']['category_id'];
            $related = $articleModel->getRelatedArticles(
                $catId,
                $id,
                4
            );
            $prevNext = $articleModel->getPrevNextInCategory(
                $catId,
                (string)($details['article']['created_at'] ?? ''),
                $id
            );
        }
        
        $comments = (new CommentModel())->listForArticle($id);
        $this->view('article/show', [
            'article' => $details['article'],
            'comments' => $comments,
            'articleContent' => $details['content'],
            'images' => $details['images'],
            'readingMinutes' => $readingMinutes,
            'related' => $related,
            'prevNext' => $prevNext
        ]);
    }

    public function category(int $id): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = 9;

        // Lay thong tin danh muc
        $categoryModel = new CategoryModel();
        $category = $categoryModel->find($id);

        // Lay danh sach bai viet va tong so luong
        [$articles, $total] = (new ArticleModel())->getByCategory($id, $page, $per);
        $pages = (int)ceil($total / $per);

        // Hien thi giao dien
        $this->view('article/category', [
            'category' => $category,
            'articles' => $articles,
            'page' => $page,
            'pages' => $pages
        ]);
    }
}
