<p align="center">
	<img src="public/images/memehub-wordmark.svg" alt="MemeHub" width="420" />
</p>

# MemeHub

A Laravel-based meme-sharing platform with Google Login, media uploads, reporting workflow, and admin moderation.

Current version: `v1.3.6`

## Screenshots

| Feed / Home | Detail Meme |
| :---: | :---: |
| ![Feed](public/images/screenshot-feed.png) | ![Detail](public/images/screenshot-detail.png) |
| **Login Page** | **Upload Modal** |
| ![Login](public/images/screenshot-login.png) | ![Upload](public/images/screenshot-upload.png) |

## Quick Start (No Redis Needed)

1. **Install dependencies**:
   ```bash
   composer install
   npm install && npm run build
   ```
2. **Setup environment**:
   Copy `.env.example` to `.env` and configure your database (`DB_*`).
3. **Run migrations**:
   ```bash
   php artisan migrate
   ```
4. **Create storage symlink** *(required for image uploads)*:
   ```bash
   php artisan storage:link
   ```
5. **Run development server**:
   ```bash
   php artisan serve
   ```

## Key Optimizations
- **No Redis Dependency**: Default drivers are configured to use `file` and `database` for instant deployment.
- **Media Compression**: Uploaded images are automatically converted to optimized WebP, and GIFs are resized and compressed to reduce server bandwidth usage.
- **Database Indexed**: Search and feeds are optimized with indexes on `score` and `created_at`.

## Server Requirements
- PHP `^8.1` with extensions: **`gd`** or **`imagick`** (for image upload/WebP conversion), `fileinfo`, `pdo_mysql`
- MySQL or MariaDB
- Optional: `ffmpeg` for GIF & video compression

## License

This project is licensed under the [MIT License](LICENSE).
