<?php
$pagination = $pagination ?? null;
if (!$pagination || ($pagination['totalPages'] ?? 1) <= 1) {
    return;
}

$current = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['totalPages'] ?? 1);
$params = $pagination['params'] ?? [];

$buildUrl = function (int $page) use ($params, $baseUrl): string {
    return $baseUrl . '/index.php?' . http_build_query(array_merge($params, ['page' => $page]));
};

$pages = [1];
for ($page = $current - 2; $page <= $current + 2; $page++) {
    if ($page > 1 && $page < $totalPages) {
        $pages[] = $page;
    }
}
if ($totalPages > 1) {
    $pages[] = $totalPages;
}

$pages = array_values(array_unique($pages));
sort($pages);
$lastPage = 0;
?>

<nav aria-label="Paginación" class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $current <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo htmlspecialchars($buildUrl(max(1, $current - 1))); ?>" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php foreach ($pages as $page): ?>
            <?php if ($lastPage && $page > $lastPage + 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item <?php echo $page === $current ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo htmlspecialchars($buildUrl($page)); ?>"><?php echo $page; ?></a>
            </li>
            <?php $lastPage = $page; ?>
        <?php endforeach; ?>
        <li class="page-item <?php echo $current >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo htmlspecialchars($buildUrl(min($totalPages, $current + 1))); ?>" aria-label="Siguiente">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>