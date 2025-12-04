# Original: https://github.com/bimmerM5/News_web (by Bimmer, dahikuv, minjion)
# News Web (PHP MVC)

Cổng thông tin tức thuần PHP (PDO + MySQL) theo mô hình MVC. Ứng dụng gồm trang công khai, đăng ký/đăng nhập, trang quản trị bài viết/danh mục, upload nhiều ảnh với tùy chọn hiển thị, tìm kiếm, bình luận AJAX, đếm like/xem và API JSON. Giao diện Bootstrap 5 + dark/light toggle.

## Mục lục
- Tính năng
- Công nghệ & kiến trúc
- Cấu trúc thư mục
- Cài đặt nhanh
- Cấu hình ứng dụng
- Cơ sở dữ liệu
- Định tuyến
- Ảnh & upload
- Ghi chú phát triển
- Khắc phục sự cố

## Tính năng
- Trang công khai: trang chủ, danh mục, chi tiết bài viết, tìm kiếm.
- Xác thực: đăng ký, đăng nhập, đăng xuất (session-based).
- Quản trị: tạo/sửa/xuất bản/xóa bài viết; quản lý danh mục; dashboard; chỉ admin mới truy cập.
- Nội dung bài viết: trình bày HTML, đính kèm nhiều ảnh, chỉnh kích thước/căn lề/caption từng ảnh, xóa ảnh sẽ xóa cả file vật lý.
- Tương tác: bình luận gửi qua AJAX (cần đăng nhập), đếm lượt thích/lượt xem.
- API JSON: danh sách bài viết, chi tiết, bình luận, toggle like, kiểm tra username/email.
- Giao diện: Bootstrap 5.3 CDN + `public/assets/css/style.css`, hỗ trợ chuyển dark/light bằng localStorage.

## Công nghệ & kiến trúc
- PHP 8.x, PDO kết nối MySQL/MariaDB.
- Apache + mod_rewrite (khuyến nghị XAMPP trên Windows).
- Kiến trúc MVC tự viết (không Composer): Router → Controller → Model → Queries/Repository.
- View PHP thuần trong `app/Views` (layout header/footer dùng chung).
- Repository Pattern tách truy vấn SQL trong `app/Queries/*`.

## Cấu trúc thư mục
```
app/
  Config/config.php          # cấu hình DB + base_url, debug
  Core/                      # Router, Controller, Database
  Controllers/               # Home, Article, Admin, Auth, Api, Search, Profile
  Models/                    # Article, Category, Comment, User + BaseModel
  Queries/                   # QueryBuilder + *Queries + *Repository
  Views/                     # layout + trang công khai, auth, admin
database/
  news_portal.sql            # schema, dữ liệu mẫu, stored procedures, event
public/
  index.php                  # front controller & định tuyến
  .htaccess                  # rewrite về index.php (chỉnh RewriteBase)
  assets/css/style.css       # CSS tùy biến + class ảnh + theme toggle
  uploads/                   # nơi lưu file ảnh upload (tạo nếu thiếu)
```

## Cài đặt nhanh
1) Yêu cầu: PHP 8.1+ (có PDO MySQL), MySQL/MariaDB, Apache hỗ trợ rewrite.
2) Đặt mã nguồn vào web root (ví dụ XAMPP): `C:\xampp\htdocs\news_web`.
3) Cấu hình ứng dụng trong `app/Config/config.php`:
```php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'news_portal',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '/news_web/public', // đổi theo alias/đường dẫn của bạn
        'debug' => true,
    ],
];
```
4) Import cơ sở dữ liệu: mở phpMyAdmin (hoặc CLI) và import `database/news_portal.sql` vào DB `news_portal` (tạo DB trước nếu chưa có).
5) Cấu hình rewrite: mở `public/.htaccess` và chỉnh `RewriteBase` khớp `base_url`, ví dụ:
```
RewriteBase /news_web/public/
```
6) Phân quyền upload: đảm bảo `public/uploads` tồn tại và web server có quyền ghi.
7) Chạy ứng dụng: truy cập `http://localhost/news_web/public` (hoặc theo `base_url` bạn đặt).
8) Tài khoản admin: tạo tài khoản qua trang đăng ký rồi gán vai trò admin trong DB:
```sql
INSERT INTO roles (role_name) VALUES ('admin') ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);
INSERT INTO user_roles (user_id, role_id)
VALUES (<USER_ID_CUA_BAN>, (SELECT role_id FROM roles WHERE role_name='admin'))
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);
```

## Cơ sở dữ liệu
- Bảng chính: `articles`, `article_contents`, `article_media` (lưu media_url/media_type/caption/size_class/align_class), `categories`, `users`, `user_profiles`, `roles`, `user_roles`, `comments`, `likes`, `views`, `tags`, `article_tags`.
- Stored procedure nổi bật: `sp_create_article`, `sp_publish_article`, `sp_add_comment`, `sp_get_comments`, `sp_get_user_articles`, `sp_get_articles_by_category`, `sp_toggle_like`.
- Event: `ev_clean_old_data` (dọn dữ liệu view định kỳ).

## Định tuyến
Được khai báo tại `public/index.php`:
- Public: `GET /`, `/article/{id}`, `/category/{id}`, `/search`, `/user/{id}`.
- Auth: `GET /auth/login`, `POST /auth/login`, `GET /auth/register`, `POST /auth/register`, `POST /auth/logout`.
- Admin: `GET /admin/categories`, `/admin/categories/create`, `POST /admin/categories/store`, `GET /admin/categories/{id}/edit`, `POST /admin/categories/{id}/update`, `POST /admin/categories/{id}/delete`; tương tự cho bài viết `/admin/articles` + `/publish`.
- API (JSON): `GET /api/articles`, `/api/article/{id}`, `/api/comments?article_id={id}`, `POST /api/comments`, `POST /api/toggle-like`, `GET /api/check-availability`.

## Ảnh & upload
- File upload được đặt tên `img_YYYYMMDD_HHMMSS_<rand>.ext` và lưu ở `public/uploads`.
- Thuộc tính hiển thị lưu trong bảng `article_media`:
  - `size_class`: `.img-small` | `.img-medium` | `.img-large`
  - `align_class`: `.img-left` | `.img-center` | `.img-right`
  - `caption`: chú thích (tùy chọn)
- Frontend render bằng `<figure>/<figcaption>` với class tương ứng (xem `app/Views/article/show.php`); CSS nằm trong `public/assets/css/style.css`.
- Xóa ảnh trong trang sửa bài sẽ xóa cả bản ghi DB và file vật lý.

## Ghi chú phát triển
- Không dùng Composer/autoloader; các file được require thủ công tại `public/index.php`.
- Router tự viết hỗ trợ regex đơn giản và tự loại bỏ `base_url`.
- View chia layout header/footer; biến `$baseUrl` được truyền sẵn cho tài nguyên tĩnh và link.
- Các truy vấn SQL tập trung ở `app/Queries/*`; Models gọi Repository để hạn chế lặp lại SQL.
- Mặc định đếm lượt xem/like dựa trên session user_id; cân nhắc thêm CSRF/token khi mở rộng.

## Khắc phục sự cố
- 404 sau khi đổi thư mục/alias: cập nhật `app/Config/config.php` → `base_url` và `public/.htaccess` → `RewriteBase`.
- Lỗi kết nối DB: kiểm tra thông tin trong `config.php`, chắc chắn đã import `database/news_portal.sql`.
- Upload lỗi quyền: tạo `public/uploads` và cấp quyền ghi cho user chạy web server.
