# Changelog

## [v1.3.3] - 2026-03-05

### Added
- Documented production dependency requirements for media conversion on VPS: FFmpeg binary must be installed and configured via `FFMPEG_BIN`.

### Fixed
- Report submissions from meme posts now always flow into moderation queue.
- Re-reporting a previously reviewed or rejected meme now re-opens the report as `pending` for moderator review.

## [v1.3.2] - 2026-03-05

### Fixed
- Replaced report dropdown on meme detail and home feed with popup modal flow.
- Fixed report popup layering so modal always appears above meme/media elements.
- Fixed report action behavior on home feed so it opens immediately without re-triggering the action menu.

### Changed
- Settings page now shows app version and latest changelog summary in a unified version panel.
- Documentation updated: README simplified in English and installation guide moved to GitHub Wiki.

## [v1.3.0] - 2026-03-05

### Changed
- Responsive navbar desktop/mobile refined to prevent layout collisions.
- Brand dropdown (`MemeHub`) now appears on mobile only; desktop uses plain title.
- Mobile panel transitions (brand/search/notifications/menu) smoothed with fade-slide animation.
- Feed interest sidebar remains desktop-only to avoid duplicate navigation on mobile.
