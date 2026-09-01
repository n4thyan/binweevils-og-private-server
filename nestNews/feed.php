<?php
error_reporting(0);
include('../essential/backbone.php');
include('../site/news.php');

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

function news_xml_text($value) {
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function news_xml_cdata($value) {
    return str_replace(']]>', ']]]]><![CDATA[>', (string)$value);
}

$articles = site_news_articles(25);
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<nestNews>\n";
echo '  <topstory>' . (!empty($articles) && !empty($articles[0]['is_top_story']) ? 'y' : '') . "</topstory>\n";

foreach($articles as $article) {
    $category = max(1, min(5, (int)$article['category']));
    $startDate = date('d/m/Y', (int)$article['published_at']);
    $endDate = !empty($article['expires_at']) ? date('d/m/Y', (int)$article['expires_at']) : '';
    echo "  <newsItem>\n";
    echo '    <startDate>' . $startDate . "</startDate>\n";
    echo '    <endDate>' . $endDate . "</endDate>\n";
    echo '    <category>' . $category . "</category>\n";
    echo '    <headline><![CDATA[' . news_xml_cdata($article['title']) . "]]></headline>\n";
    echo '    <bodyText><![CDATA[' . news_xml_cdata($article['body']) . "]]></bodyText>\n";
    echo '    <pic>' . news_xml_text($article['image_path'] ?? '') . "</pic>\n";
    echo "    <links>\n";
    foreach(site_news_links((int)$article['id']) as $link) {
        echo "      <link>\n";
        echo '        <linkTarget>' . news_xml_text($link['link_target']) . "</linkTarget>\n";
        echo '        <linkType>' . max(1, min(3, (int)$link['link_type'])) . "</linkType>\n";
        echo '        <trackingID>' . news_xml_text($link['tracking_id'] ?? '') . "</trackingID>\n";
        echo "      </link>\n";
    }
    echo "    </links>\n";
    echo "  </newsItem>\n";
}

echo "</nestNews>\n";
?>
