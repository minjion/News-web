# News Web (PHP MVC)

A plain PHP news portal (PDO + MySQL) following the MVC pattern. The application includes a public site, registration/login, an admin area for posts/categories, multiple image uploads, and more.

## Table of contents
- Features
- Technology & architecture
- Directory structure
- Quick installation
- Application configuration
- Database
- Routing
- Images & upload
- Development notes
- Troubleshooting

## Features
- Public site: homepage, category pages, article detail, search.
- Authentication: register, login, logout (session-based).
- Admin: create/edit/publish/delete articles; manage categories; dashboard; admin-only access.
- Article content: HTML content, attach multiple images, set size/alignment/caption per image, deleting an image removes the physical file as well.
- Interaction: comments submitted via AJAX (login required), like/view counters.
- JSON API: list articles, article detail, comments, toggle like, check username/email availability.
- UI: Bootstrap 5.3 CDN + `public/assets/css/style.css`, supports dark/light toggle using localStorage.

## Technology & architecture
- PHP 8.x, PDO connecting to MySQL/MariaDB.
- Apache + mod_rewrite (XAMPP on Windows recommended).
- Custom MVC architecture (no Composer): Router → Controller → Model → Queries/Repository.
- Plain PHP Views in `app/Views` (shared header/footer layout).
- Repository Pattern: SQL queries isolated in `app/Queries/*`.

## Directory structure
```
app/
  Config/config.php          # app configuration: DB + base_url, debug
  Core/                      # Router, Controller, Database
  Controllers/               # Home, Article, Admin, Auth, Api, Search, Profile
  Models/                    # Article, Category, Comment, User + BaseModel
  Queries/                   # QueryBuilder + *Queries + *Repository
  Views/                     # layout + public pages, auth, admin
database/
  news_portal.sql            # schema, sample data, stored procedures, event
public/
  index.php                  # front controller & routing
  .htaccess                  # rewrite to index.php (adjust RewriteBase)
  assets/css/style.css       # custom CSS + image classes + theme toggle
  uploads/                   # directory to store uploaded images (create if missing)
```

## Quick installation
1) Requirements: PHP 8.1+ (with PDO MySQL), MySQL/MariaDB, Apache with rewrite support.
2) Place the source code into your web root (e.g., XAMPP): `C:\xampp\htdocs\news_web`.
3) Configure the application in `app/Config/config.php`:
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
        'base_url' => '/news_web/public', // change according to your alias/path
        'debug' => true,
    ],
];
```
4) Import the database: open phpMyAdmin (or CLI) and import `database/news_portal.sql` into a database named `news_portal` (create the DB first if it doesn't exist).
5) Configure rewrite: open `public/.htaccess` and set `RewriteBase` to match `base_url`, for example:
```
RewriteBase /news_web/public/
```
6) Upload permissions: ensure `public/uploads` exists and the web server user has write permission.
7) Run the app: visit `http://localhost/news_web/public` (or the `base_url` you set).
8) Admin account: create an account via the registration page, then grant the admin role in the DB:
```sql
INSERT INTO roles (role_name) VALUES ('admin') ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);
INSERT INTO user_roles (user_id, role_id)
VALUES (<YOUR_USER_ID>, (SELECT role_id FROM roles WHERE role_name='admin'))
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);
```

## Database
- Main tables: `articles`, `article_contents`, `article_media` (stores media_url/media_type/caption/size_class/align_class), `categories`, `users`, `user_profiles`, `roles`, `user_roles`, `comments`, [...]
- Notable stored procedures: `sp_create_article`, `sp_publish_article`, `sp_add_comment`, `sp_get_comments`, `sp_get_user_articles`, `sp_get_articles_by_category`, `sp_toggle_like`.
- Event: `ev_clean_old_data` (periodic cleanup of view data).

## Routing
Declared in `public/index.php`:
- Public: `GET /`, `/article/{id}`, `/category/{id}`, `/search`, `/user/{id}`.
- Auth: `GET /auth/login`, `POST /auth/login`, `GET /auth/register`, `POST /auth/register`, `POST /auth/logout`.
- Admin: `GET /admin/categories`, `/admin/categories/create`, `POST /admin/categories/store`, `GET /admin/categories/{id}/edit`, `POST /admin/categories/{id}/update`, `POST /admin/categories/{id}/delete`, [...]
- API (JSON): `GET /api/articles`, `/api/article/{id}`, `/api/comments?article_id={id}`, `POST /api/comments`, `POST /api/toggle-like`, `GET /api/check-availability`.

## Images & upload
- Uploaded files are named `img_YYYYMMDD_HHMMSS_<rand>.ext` and stored in `public/uploads`.
- Display attributes are stored in `article_media` table:
  - `size_class`: `.img-small` | `.img-medium` | `.img-large`
  - `align_class`: `.img-left` | `.img-center` | `.img-right`
  - `caption`: optional caption
- Frontend renders using `<figure>/<figcaption>` with the corresponding classes (see `app/Views/article/show.php`); CSS lives in `public/assets/css/style.css`.
- Deleting an image in the article edit page removes both the DB record and the physical file.

## Development notes
- No Composer/autoloading is used; files are required manually in `public/index.php`.
- The custom router supports simple regex and automatically strips the `base_url`.
- Views are split into layout header/footer; `$baseUrl` is provided to templates for static resources and links.
- SQL queries are centralized in `app/Queries/*`; Models call Repositories to reduce duplicated SQL.
- View/like counters default to session-based user_id tracking; consider adding CSRF/tokens when extending.

## Troubleshooting
- 404 after changing directory/alias: update `app/Config/config.php` → `base_url` and `public/.htaccess` → `RewriteBase`.
- DB connection error: check the info in `config.php` and ensure `database/news_portal.sql` has been imported.
- Upload permission errors: create `public/uploads` and give write permission to the user running the web server.
