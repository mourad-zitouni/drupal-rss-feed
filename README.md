# drupal-rss-feed
# Install the project:
1. clone the repository.
2. cd drupal-rss-feed
3. ddev start
4. ddev composer install
5. Import database:
    ddev import-db --src=db-backup/dump.sql
9. Clear drupal cache:
    ddev drush cr
10. Go to https://drupal-rss-feed.ddev.site
11. User login: admin/admin


