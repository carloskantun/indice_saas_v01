<?php
/**
 * Selector de idioma para incluir en layouts
 */
$current_lang = getCurrentLanguage();
$available_languages = getAvailableLanguages();
?>

<div class="dropdown">
    <button class="btn btn-link nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-translate"></i>
        <?= $available_languages[$current_lang] ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($available_languages as $code => $name): ?>
            <li>
                <a class="dropdown-item <?= $code === $current_lang ? 'active' : '' ?>" 
                   href="/set-language.php?lang=<?= $code ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                    <?= $name ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
