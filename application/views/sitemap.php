<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <?php foreach ($pages as $url): ?>
    <url>
      <loc><?= htmlspecialchars($url) ?></loc>
      <lastmod><?= $lastmod ?></lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
    </url>
  <?php endforeach; ?>
</urlset>