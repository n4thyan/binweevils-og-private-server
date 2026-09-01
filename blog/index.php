<?php
// Keep the historical website news route pointing at the shared DB-backed Bulletin.
header('Location: /bulletin/', true, 302);
exit;
?>
