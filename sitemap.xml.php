<?php
declare(strict_types=1);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'excelsnake.online';
$base = $scheme . '://' . $host;

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= htmlspecialchars($base . '/index.php', ENT_XML1, 'UTF-8') ?></loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($base . '/guia-excel.php', ENT_XML1, 'UTF-8') ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($base . '/leaderboard.php', ENT_XML1, 'UTF-8') ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($base . '/privacy.php', ENT_XML1, 'UTF-8') ?></loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($base . '/cookies.php', ENT_XML1, 'UTF-8') ?></loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
</urlset>
